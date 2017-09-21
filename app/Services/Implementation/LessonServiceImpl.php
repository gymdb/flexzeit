<?php

namespace App\Services\Implementation;

use App\Exceptions\LessonException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\LessonRepository;
use App\Repositories\RegistrationRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Services\WebUntisService;
use Illuminate\Support\Facades\Log;

class LessonServiceImpl implements LessonService {

  /** @var ConfigService */
  private $configService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var WebUntisService */
  private $untisService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var RegistrationRepository */
  private $registrationRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param RegistrationService $registrationService
   * @param WebUntisService $untisService
   * @param LessonRepository $lessonRepository
   * @param RegistrationRepository $registrationRepository
   */
  public function __construct(ConfigService $configService, RegistrationService $registrationService, WebUntisService $untisService,
      LessonRepository $lessonRepository, RegistrationRepository $registrationRepository) {
    $this->configService = $configService;
    $this->registrationService = $registrationService;
    $this->untisService = $untisService;
    $this->lessonRepository = $lessonRepository;
    $this->registrationRepository = $registrationRepository;
  }

  public function cancelLesson(Lesson $lesson) {
    if ($lesson->date->isPast()) {
      throw new LessonException(LessonException::CANCEL_PERIOD);
    }

    $lesson->cancelled = true;
    $lesson->save();
  }

  public function reinstateLesson(Lesson $lesson) {
    if ($lesson->date->isPast()) {
      throw new LessonException(LessonException::CANCEL_PERIOD);
    }

    $this->registrationRepository->deleteDuplicate($lesson);
    $lesson->cancelled = false;
    $lesson->substitute_id = null;
    $lesson->save();
  }

  public function loadSubstitutions() {
    // Get start and end date for Untis query
    $start = $this->configService->getYearStart(Date::today());
    $end = $this->configService->getYearEnd();

    // Get global lesson times
    $times = $this->configService->getLessonTimes();

    // Load list of teachers by shortname
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $teachers = Teacher::get(['id', 'shortname'])->buildDictionary(['shortname'], false);

    // Load and map substitution data from WebUntis
    $substitutions = $this->untisService
        ->getSubstitutions($start, $end)
        ->flatMap(function($substitution) use ($times, $teachers) {
          $date = Date::instance($substitution['start']);
          if (empty($times[$date->dayOfWeek])) {
            // Ignore lessons if there is no flex on the given day
            return [];
          }

          $teacher = $teachers[$substitution['teacher']] ?? null;
          $originalTeacher = $substitution['originalTeacher'] ? ($teachers[$substitution['originalTeacher']] ?? null) : null;
          $result = [];
          foreach ($times[$date->dayOfWeek] as $n => $time) {
            if ($date->toDateTime($time['start']) >= $substitution['start'] && $date->toDateTime($time['end']) <= $substitution['end']) {
              if (!$teacher) {
                Log::warning("Could not find teacher with shortname {$substitution['teacher']} for lesson {$date->toDateString()}/{$n}.");
                continue;
              }

              switch ($substitution['type']) {
                case 'cancel':
                  $result[] = [
                      'subst'   => false,
                      'date'    => $date,
                      'number'  => $n,
                      'teacher' => $teacher
                  ];
                  break;
                case 'subst':
                  if (!$originalTeacher) {
                    Log::warning("Could not find teacher with shortname {$substitution['originalTeacher']} for lesson {$date->toDateString()}/{$n}.");
                    break;
                  }
                  $result[] = [
                      'subst'      => true,
                      'date'       => $date,
                      'number'     => $n,
                      'teacher'    => $originalTeacher,
                      'newTeacher' => $teacher
                  ];
                  break;
                default:
                  Log::warning("Unknown substitution type '{$substitution['type']}' for teacher {$substitution['teacher']} for lesson {$date->toDateString()}/{$n}.");
                  break;
              }
            }
          }
          return $result;
        });

    // Load the lessons corresponding to the substitution data
    $lessons = $this->lessonRepository->queryForSubstitutions($substitutions)
        ->get(['id', 'teacher_id', 'date', 'number', 'cancelled', 'substitute_id', 'room_id'])
        ->buildDictionary(['teacher_id', 'date', 'number'], false);

    $substitutions->each(function($substitution) use ($lessons) {
      $lesson = $lessons->nestedGet([$substitution['teacher']->id, $substitution['date']->toDateString(), $substitution['number']]);
      if (!$lesson) {
        // The lesson to substitute does not exist
        Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} does not exist.");
        return;
      }

      if ($substitution['subst']) {
        // Lesson is substituted by another teacher
        if ($lesson->substitute_id) {
          // Lesson is already marked as substituted
          if ($lesson->substitute_id !== $substitution['newTeacher']->id) {
            // Substitute teacher has changed, log a warning
            Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be substituted by teacher {$substitution['newTeacher']->shortname}, but is already substituted by #{$lesson->substitute_id}.");
          }
          return;
        }

        $newLesson = $lessons->nestedGet([$substitution['newTeacher']->id, $substitution['date']->toDateString(), $substitution['number']]);
        if ($newLesson && $newLesson->cancelled) {
          // The teacher supposed to substitute has a cancelled lesson at the given time, log a warning and only cancel
          Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be substituted by teacher {$substitution['newTeacher']->shortname}, but that lesson is also marked as cancelled.");
          $this->cancelLesson($lesson);
        } else {
          $this->substituteLesson($lesson, $substitution['newTeacher']);
        }
      } else {
        // Lesson should be cancelled
        if ($lesson->substitute_id) {
          Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be cancelled, but is already substituted by #{$lesson->substitute_id}.");
        } else if (!$lesson->cancelled) {
          // Only cancel if not already cancelled
          $this->cancelLesson($lesson);
        }
      }
    });
  }

  public function getMappedForTeacher(Teacher $teacher = null, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    $query = $this->lessonRepository
        ->queryForTeacher($teacher, $start, $end, $dayOfWeek, $number, $showCancelled, $withCourse)
        ->with('course:id,name,maxstudents', 'room:id,name,capacity', 'teacher:id,lastname,firstname')
        ->select('lessons.id', 'date', 'number', 'cancelled', 'course_id', 'room_id', 'teacher_id');

    if (!$teacher) {
      $query->join('teachers as t', 't.id', 'lessons.teacher_id')
          ->orderBy('t.lastname')
          ->orderBy('t.firstname');
    }

    return $this->lessonRepository
        ->addParticipants($query)
        ->get()
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);

          $data = [
              'id'           => $lesson->id,
              'date'         => $lesson->date->toDateString(),
              'time'         => $lesson->time,
              'cancelled'    => $lesson->cancelled,
              'participants' => $lesson->participants,
              'room'         => $lesson->room->name,
              'teacher'      => $lesson->teacher->name()
          ];
          if ($lesson->course) {
            $data['course'] = [
                'id'   => $lesson->course->id,
                'name' => $lesson->course->name
            ];
            $data['maxstudents'] = $lesson->course->maxstudents;
          } else {
            $data['maxstudents'] = $lesson->room->capacity;
          }
          return $data;
        });
  }

  public function getForDay(Teacher $teacher, Date $date = null) {
    $query = $this->lessonRepository
        ->queryForTeacher($teacher, $date ?: Date::today(), null, null, null, true)
        ->with('course:id,name,maxstudents', 'room:id,name,capacity')
        ->select('id', 'number', 'cancelled', 'course_id', 'room_id');

    return $this->lessonRepository
        ->addParticipants($query)
        ->get()
        ->each(function(Lesson $lesson) {
          $this->configService->setTime($lesson);
        });
  }

  public function getForCourse(Course $course) {
    $lessons = $course->lessons()
        ->orderBy('date')
        ->orderBy('number')
        ->get(['id', 'date', 'number', 'cancelled', 'room_id', 'teacher_id']);
    $lessons->each(function(Lesson $lesson) {
      $this->configService->setTime($lesson);
    });
    return $lessons;
  }

  public function getAvailableLessons(Student $student, Date $date, Teacher $teacher = null, Subject $subject = null, $type = null) {
    $lessons = $this->lessonRepository
        ->queryAvailable($student, $date, $teacher, $subject, $type)
        ->addSelect(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.room_id', 'lessons.teacher_id', 'lessons.course_id'])
        ->with(['course.lessons' => function($query) {
          $query->orderBy('date')->orderBy('number')->select('id', 'date', 'course_id');
        }])
        ->with('room:id,name', 'teacher:id,lastname,firstname,info,image', 'teacher.subjects', 'course:id,name,description')
        ->get()
        ->map(function(Lesson $lesson) use ($student) {
          $this->configService->setTime($lesson);

          $data = [
              'id'      => $lesson->id,
              'date'    => $lesson->date->toDateString(),
              'time'    => $lesson->time,
              'room'    => $lesson->room->name,
              'teacher' => [
                  'name'     => $lesson->teacher->name(),
                  'image'    => $lesson->teacher->image ? url($lesson->teacher->image) : null,
                  'info'     => $lesson->teacher->info,
                  'subjects' => $lesson->teacher->subjects->implode('name', ', ')
              ]
          ];
          if ($lesson->course) {
            $associated = $lesson->course->lessons->map(function(Lesson $l) {
              return $l->date->toDateString();
            })->unique();

            $data['course'] = [
                'id'          => $lesson->course->id,
                'name'        => $lesson->course->name,
                'description' => $lesson->course->description,
                'lessons'     => $associated
            ];
          }
          return $data;
        });

    return $lessons;
  }

  public function isAttendanceChecked(Lesson $lesson) {
    return !$lesson->registrations()->whereNull('attendance')->exists();
  }

  public function hasRegistrationsWithoutDuplicates(Lesson $lesson) {
    return $this->registrationRepository->queryNoneDuplicateRegistrations($lesson)->exists();
  }

  public function getSubstituteInformation(Lesson $lesson, Teacher $teacher) {
    if ($lesson->date->isPast()) {
      throw new LessonException(LessonException::CANCEL_PERIOD);
    }

    if ($lesson->teacher_id === $teacher->id) {
      return ['sameTeacher' => true];
    }

    $query = $this->lessonRepository
        ->queryForTeacher($teacher, $lesson->date, null, null, $lesson->number, true)
        ->with('course:id,name,maxstudents', 'room:id,name,capacity')
        ->select('id', 'cancelled', 'course_id', 'room_id');
    $teacherLesson = $this->lessonRepository
        ->addParticipants($query)
        ->get()
        ->first();
    if (!$teacherLesson) {
      return ['new' => true];
    }

    return [
        'lesson' => [
            'cancelled'    => $teacherLesson->cancelled,
            'course'       => $teacherLesson->course ? $teacherLesson->course->name : null,
            'room'         => $teacherLesson->room->name,
            'participants' => $teacherLesson->participants,
            'maxstudents'  => $teacherLesson->course ? $teacherLesson->course->maxstudents : $teacherLesson->room->capacity
        ]
    ];
  }

  public function substituteLesson(Lesson $lesson, Teacher $teacher) {
    if ($lesson->date->isPast()) {
      throw new LessonException(LessonException::CANCEL_PERIOD);
    }

    if ($lesson->teacher_id === $teacher->id) {
      throw new LessonException(LessonException::SAME_TEACHER);
    }

    $query = $this->lessonRepository
        ->queryForTeacher($teacher, $lesson->date, null, null, $lesson->number, true)
        ->select('id', 'cancelled');
    $substitutedLesson = $this->lessonRepository
        ->addParticipants($query)
        ->get()
        ->first();

    if (!$substitutedLesson) {
      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
      $substitutedLesson = Lesson::create(['date' => $lesson->date, 'number' => $lesson->number, 'room_id' => $lesson->room_id, 'teacher_id' => $teacher->id]);
    } else if ($substitutedLesson->cancelled) {
      throw new LessonException(LessonException::CANCELLED);
    }

    if (!$lesson->cancelled || $lesson->substitute_id !== $teacher->id) {
      $lesson->cancelled = true;
      $lesson->substitute_id = $teacher->id;
      $lesson->save();
    }

    $students = $this->registrationRepository->queryNoneDuplicateRegistrations($lesson)->pluck('student_id');
    if ($students->isNotEmpty()) {
      $this->registrationService->registerStudentsForLesson($substitutedLesson, $students);
    }
  }

}
