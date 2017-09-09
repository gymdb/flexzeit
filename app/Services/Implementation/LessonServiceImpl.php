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

class LessonServiceImpl implements LessonService {

  /** @var ConfigService */
  private $configService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var RegistrationRepository */
  private $registrationRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param RegistrationService $registrationService
   * @param LessonRepository $lessonRepository
   * @param RegistrationRepository $registrationRepository
   */
  public function __construct(ConfigService $configService, RegistrationService $registrationService, LessonRepository $lessonRepository, RegistrationRepository $registrationRepository) {
    $this->configService = $configService;
    $this->registrationService = $registrationService;
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
    $lesson->save();
  }

  public function getMappedForTeacher(Teacher $teacher = null, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    $query = $this->lessonRepository
        ->queryForTeacher($teacher, $start, $end, $dayOfWeek, $number, $showCancelled, $withCourse)
        ->with('course:id,name,maxstudents', 'room:id,name,capacity', 'teacher:id,lastname,firstname')
        ->select('id', 'date', 'number', 'cancelled', 'course_id', 'room_id', 'teacher_id');

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
                  'image'    => $lesson->teacher->image,
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
    $teacherLesson = $this->lessonRepository
        ->addParticipants($query)
        ->get()
        ->first();

    if (!$teacherLesson) {
      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
      $teacherLesson = Lesson::create(['date' => $lesson->date, 'number' => $lesson->number, 'room_id' => $lesson->room_id, 'teacher_id' => $teacher->id]);
    } else if ($teacherLesson->cancelled) {
      throw new LessonException(LessonException::CANCELLED);
    }

    if (!$lesson->cancelled) {
      $lesson->cancelled = true;
      $lesson->save();
    }

    $students = $this->registrationRepository->queryNoneDuplicateRegistrations($lesson)->pluck('student_id');
    if ($students->isNotEmpty()) {
      $this->registrationService->registerStudentsForLesson($teacherLesson, $students);
    }
  }

}
