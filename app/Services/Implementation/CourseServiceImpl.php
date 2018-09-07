<?php

namespace App\Services\Implementation;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Mail\ObligatoryCreated;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Offday;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\CourseRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RoomRepository;
use App\Repositories\StudentRepository;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\RegistrationService;
use App\Services\RegistrationType;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use App\Specifications\ObligatorySpecification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class CourseServiceImpl implements CourseService {

  use ServiceTrait;

  /** @var ConfigService */
  private $configService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var CourseRepository */
  private $courseRepository;

  /** @var GroupRepository */
  private $groupRepository;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var OffdayRepository */
  private $offdayRepository;

  /** @var RoomRepository */
  private $roomRepository;

  /** @var StudentRepository */
  private $studentRepository;

  function __construct(ConfigService $configService, RegistrationService $registrationService, CourseRepository $courseRepository,
      GroupRepository $groupRepository, LessonRepository $lessonRepository, OffdayRepository $offdayRepository,
      RoomRepository $roomRepository, StudentRepository $studentRepository) {
    $this->configService = $configService;
    $this->registrationService = $registrationService;
    $this->courseRepository = $courseRepository;
    $this->groupRepository = $groupRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
    $this->roomRepository = $roomRepository;
    $this->studentRepository = $studentRepository;
  }

  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher) {
    $firstDate = $spec->getFirstDate();
    $lastDate = $spec->getLastDate();
    $number = $spec->getLessonNumber();

    // Check for existing courses on one of the lessons
    $this->coursePossible($teacher, $firstDate, $lastDate, $number);

    // Load all of the teachers lessons for the given slot
    $lessons = $this->buildLessonsForCourse($teacher, $firstDate, $lastDate, $number, $spec->getRoom());

    $groups = null;
    if ($spec instanceof ObligatorySpecification) {
      // Check for existing courses for one of the groups
      $groups = $spec->getGroups();
      $this->obligatoryPossible($groups, $lessons);
      $this->obligatoryWithinTimetable($groups, $firstDate, $number);
    }

    $course = $spec->populateCourse();
    if (!$course->save()) {
      throw new CourseException(CourseException::SAVE_FAILED);
    }

    $this->assignCourse($lessons, $course);

    $course->lessons()->update(['room_id' => $spec->getRoom()]);

    if ($spec instanceof ObligatorySpecification) {
      $course->groups()->sync($groups);
      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
      $course->subject()->associate(Subject::find($spec->getSubject()));
      $course->save();

      $students = $this->studentRepository->queryForGroups($groups)->pluck('id');
      $this->registrationService->registerStudentsForCourse($course, $students, RegistrationType::OBLIGATORY());

      Mail::to($this->configService->getNotificationRecipients())
          ->send(new ObligatoryCreated($teacher, $course));
    }

    return $course;
  }

  public function editCourse(EditCourseSpecification $spec, Course $course) {
    // Check if correct specification is used
    $oldGroups = $course->groups()->allRelatedIds();
    if (($spec instanceof ObligatorySpecification) !== $oldGroups->isNotEmpty()) {
      throw new CourseException(CourseException::EDIT_SPEC);
    }

    $firstCreateDate = $this->configService->getFirstCourseCreateDate();

    $teacher = $course->teacher()->first();
    $lastDate = $spec->getLastDate();

    $firstLesson = $course->firstLesson();
    $lastLesson = $course->lastLesson();

    $firstDate = $firstLesson->date;
    $oldLastDate = $lastLesson->date;
    $number = $firstLesson->number;

    // Load first modified date
    $firstChanged = $this->getFirstChangedDate($oldLastDate, $lastDate);
    $addedLessons = collect([]);
    if ($firstChanged) {
      // Check if changing course duration is still possible
      if ($oldLastDate->copy()->addWeek() < $firstCreateDate) {
        throw new CourseException(CourseException::EDIT_PERIOD);
      }

      // Check for existing courses on one of the newly added lessons
      if ($firstChanged > $oldLastDate) {
        $this->coursePossible($teacher, $firstChanged, $lastDate, $number);
        $addedLessons = $this->buildLessonsForCourse($teacher, $firstChanged, $lastDate, $number, $spec->getRoom());
      }
    }

    // Load group changes
    $groups = $groupsChanged = null;
    if ($spec instanceof ObligatorySpecification) {
      $groups = $spec->getGroups();

      // Check for existing courses for one of the groups
      $keptLessons = $firstChanged ? $course->lessons()->where('date', '<', $firstChanged)->get(['date', 'number']) : $course->lessons;
      $this->obligatoryPossible($groups, $addedLessons->merge($keptLessons), $course);
      $this->obligatoryWithinTimetable($groups, $firstDate, $number);

      $groupsChanged = count($groups) !== $oldGroups->count() || $oldGroups->diff($groups)->isNotEmpty();
      if ($firstDate < $firstCreateDate && $groupsChanged) {
        throw new CourseException(CourseException::EDIT_GROUPS);
      }
    }

    // Save the modified course information
    $course = $spec->populateCourse($course);
    if (!$course->save()) {
      throw new CourseException(CourseException::SAVE_FAILED);
    }

    if ($firstChanged) {
      if ($firstChanged <= $oldLastDate) {
        // Remove some lessons
        $this->registrationService->unregisterAllFromCourse($course, $firstChanged);
        $course->lessons()->where('date', '>=', $firstChanged)->update(['course_id' => null]);
      } else {
        // Add lessons
        $this->assignCourse($addedLessons, $course);

        // Register students for added lessons
        $students = $course->registrations()->distinct()->pluck('student_id');
        $this->registrationService->registerStudentsForCourse($course, $students, RegistrationType::OBLIGATORY(), $firstChanged);
      }
    }

    $course->lessons()->update(['room_id' => $spec->getRoom()]);

    if ($spec instanceof ObligatorySpecification) {
      // Set new subject
      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
      $course->subject()->associate(Subject::find($spec->getSubject()));
      $course->save();

      if ($groupsChanged) {
        // Set new groups
        $course->groups()->sync($groups);

        // Calculate differences in required students
        $students = $this->studentRepository->queryForGroups($groups)->pluck('id');
        $oldStudents = $this->studentRepository->queryForGroups($oldGroups)->pluck('id');

        $addedStudents = $students->diff($oldStudents);
        $removedStudents = $oldStudents->diff($students);

        // Remove students in removed groups and add students in added groups
        if ($removedStudents->isNotEmpty()) {
          $this->registrationService->unregisterStudentsFromCourse($course, $removedStudents->all());
        }
        if ($addedStudents->isNotEmpty()) {
          $this->registrationService->registerStudentsForCourse($course, $addedStudents, RegistrationType::OBLIGATORY());
        }
      }
    }

    return $course;
  }

  public function removeCourse(Course $course) {
    if (!$course->firstLesson()->date->isFuture()) {
      throw new CourseException(CourseException::DELETE_PERIOD);
    }

    $this->registrationService->unregisterAllFromCourse($course);
    $course->lessons()->where('generated', true)->delete();
    $course->lessons()->update(['course_id' => null]);
    $course->groups()->sync([]);
    $course->delete();
  }

  private function coursePossible(Teacher $teacher, Date $firstDate, Date $lastDate = null, $number) {
    if ($this->lessonRepository->queryForTeacher($teacher, $firstDate, $lastDate, $firstDate->dayOfWeek, $number, false, true)->exists()) {
      throw new CourseException(CourseException::EXISTS);
    }
  }

  private function obligatoryPossible(array $groups, Collection $lessons, Course $exclude = null) {
    if ($this->lessonRepository->queryForGroups($groups, $lessons, $exclude)->exists()) {
      throw new CourseException(CourseException::OBLIGATORY_EXISTS);
    }
    if ($this->offdayRepository->queryForLessonsWithGroups($lessons, $groups)->exists()) {
      throw new CourseException(CourseException::OBLIGATORY_OFFDAY);
    }
  }

  private function obligatoryWithinTimetable(array $groups, Date $date, $number) {
    if ($this->groupRepository->queryTimetable($groups, $date->dayOfWeek, $number)->exists()) {
      throw new CourseException(CourseException::NOT_IN_TIMETABLE);
    }
  }

  private function buildLessonsForCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers, $room = null, $withCancelled = false) {
    if (is_scalar($numbers)) {
      $numbers = [$numbers];
    }
    $dayOfWeek = $firstDate->dayOfWeek;

    $lessons = $this->lessonRepository
        ->queryForTeacher($teacher, $firstDate, $lastDate, $dayOfWeek, $numbers, true)
        ->get(['id', 'date', 'number', 'cancelled']);

    if (!$lessons->contains($this->matcher($firstDate, null))) {
      // Teacher has no lesson at the first course date, so create them for all days except offdays
      $offdays = $this->offdayRepository->queryInRange($firstDate, $lastDate, $dayOfWeek, $numbers)->get(['date', 'number']);

      for ($d = $firstDate; $d <= ($lastDate ?: $firstDate); $d = $d->copy()->addWeek()) {
        foreach ($numbers as $n) {
          if ($offdays->contains($this->matcher($d, $n))) {
            continue;
          }
          if (!$lessons->contains($this->matcher($d, $n))) {
            $lesson = new Lesson(['date' => $d, 'number' => $n, 'generated' => true, 'room_id' => $room]);
            $lesson->teacher()->associate($teacher);
            $lessons->push($lesson);
          }
        }
      }
    }

    return $withCancelled
        ? $lessons
        : $lessons->filter(function(Lesson $lesson) {
          return empty($lesson->cancelled);
        });
  }

  private function assignCourse(Collection $lessons, Course $course) {
    $this->lessonRepository->assignCourse($lessons->where('exists', true), $course);
    $this->lessonRepository->createWithCourse($lessons->where('exists', false), $course);
  }

  public function getDataForCreate(Teacher $teacher, Date $firstDate, Date $lastDate = null, $number, array $groups = null) {
    $withCourse = $this->getWithCourse($teacher, $firstDate, $lastDate, $number);
    if ($withCourse->isNotEmpty()) {
      return compact('withCourse');
    }

    $lessonsWithCancelled = $this
        ->buildLessonsForCourse($teacher, $firstDate, $lastDate, $number, null, true)
        ->groupBy(function(Lesson $lesson) {
          return $lesson->cancelled ? 'cancelled' : 'held';
        });

    $lessons = $lessonsWithCancelled->get('held') ?: collect([]);
    if ($groups) {
      $withObligatory = $this->getWithObligatory($groups, $lessons);
      if ($withObligatory->isNotEmpty()) {
        return compact('withObligatory');
      }

      $timetable = $this->getTimetable($groups, $firstDate, $number);
      if ($timetable->isNotEmpty()) {
        return compact('timetable');
      }

      $offdays = $this->getOffdays($groups, $lessons);
      if ($offdays->isNotEmpty()) {
        return compact('offdays');
      }
    }

    $forNewCourse = $this->mapLessons($lessons);
    $cancelled = $this->mapLessons($lessonsWithCancelled->get('cancelled') ?: collect([]));

    $roomOccupation = $this->getRoomOccupation($lessons, $teacher);
    $room = $this->getDefaultRoom($teacher, $firstDate, $lastDate, $number);

    return compact('forNewCourse', 'cancelled', 'roomOccupation', 'room');
  }

  public function getDataForEdit(Course $course, Date $lastDate = null, array $groups = null) {
    $firstLesson = $course->firstLesson();
    $lastLesson = $course->lastLesson();

    $teacher = $firstLesson->teacher;

    $firstDate = $firstLesson->date;
    $number = $firstLesson->number;
    $oldLastDate = $lastLesson->date;

    $firstChanged = $this->getFirstChangedDate($oldLastDate, $lastDate);

    $allLessons = $course->lessons;
    if ($firstChanged) {
      if ($firstChanged <= $oldLastDate) {
        $lessons = $course->lessons->groupBy(function(Lesson $lesson) use ($firstChanged) {
          return $lesson->date >= $firstChanged ? 'removed' : 'kept';
        });

        $removed = $this->mapLessons($lessons['removed']);
        $allLessons = $lessons['kept'];
      } else {
        $lessonsWithCancelled = $this
            ->buildLessonsForCourse($teacher, $firstChanged, $lastDate, $number, null, true)
            ->groupBy(function(Lesson $lesson) {
              return $lesson->cancelled ? 'cancelled' : 'held';
            });
        $lessons = $lessonsWithCancelled->get('held') ?: collect([]);
        $withCourse = $this->getWithCourse($teacher, $firstChanged, $lastDate, $number);
        $added = $this->mapLessons($lessons);
        $cancelled = $this->mapLessons($lessonsWithCancelled->get('cancelled') ?: collect([]));
        $allLessons = $lessons->merge($course->lessons);
      }
    }

    $roomOccupation = $this->getRoomOccupation($allLessons, $teacher);

    if ($groups) {
      $withObligatory = $this->getWithObligatory($groups, $allLessons, $course);
      $timetable = $this->getTimetable($groups, $firstDate, $number);
      $offdays = $this->getOffdays($groups, $allLessons);
    }

    return compact('withCourse', 'added', 'removed', 'cancelled', 'withObligatory', 'timetable', 'offdays', 'roomOccupation');
  }

  private function mapLessons(Collection $lessons) {
    return $lessons
        ->sortBy('date')
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);
          return [
              'exists' => $lesson->exists,
              'date'   => $lesson->date->toDateString(),
              'time'   => $lesson->time
          ];
        })
        ->values();
  }

  private function getWithCourse(Teacher $teacher = null, Date $firstDate, Date $lastDate = null, $number) {
    return $this->lessonRepository
        ->queryForTeacher($teacher, $firstDate, $lastDate, $firstDate->dayOfWeek, $number, false, true)
        ->with('course:id,name')
        ->get(['id', 'date', 'number', 'course_id'])
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);
          return [
              'date'   => $lesson->date->toDateString(),
              'time'   => $lesson->time,
              'course' => $lesson->course->name
          ];
        });
  }

  private function getWithObligatory(array $groups, Collection $lessons, Course $exclude = null) {
    return $this->lessonRepository
        ->queryForGroups($groups, $lessons, $exclude)
        ->with('course', 'course.groups')
        ->get(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.course_id'])
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);
          return [
              'date'   => $lesson->date->toDateString(),
              'time'   => $lesson->time,
              'course' => $lesson->course->name,
              'groups' => $lesson->course->groups->pluck('name')
          ];
        });
  }

  private function getOffdays(array $groups, Collection $lessons) {
    return $this->offdayRepository
        ->queryForLessonsWithGroups($lessons, $groups)
        ->with('group')
        ->get()
        ->map(function(Offday $offday) {
          return [
              'date'  => $offday->date->toDateString(),
              'group' => $offday->group->name
          ];
        });
  }

  private function getTimetable(array $groups, Date $date, $number) {
    return $this->groupRepository
        ->queryTimetable($groups, $date->dayOfWeek, $number)
        ->orderBy('name')
        ->pluck('name');
  }

  private function getRoomOccupation(Collection $lessons, Teacher $teacher) {
    if ($lessons->isEmpty()) {
      return null;
    }

    $regularOccupations = $this->roomRepository->queryOccupationForLessons($lessons, $teacher)
        ->with('teacher:id,lastname,firstname')
        ->get(['id', 'date', 'number', 'teacher_id', 'room_id']);
    $flexOccupations = $this->lessonRepository->queryForOccupation($lessons, $teacher)
        ->with('teacher:id,lastname,firstname')
        ->get(['id', 'date', 'number', 'teacher_id', 'room_id']);

    return $regularOccupations->merge($flexOccupations)
        ->groupBy('room_id')
        ->map(function(Collection $items) {
          return $items->map(function($item) {
            $this->configService->setTime($item);
            return [
                'date'    => $item->date->toDateString(),
                'time'    => $item->time,
                'teacher' => $item->teacher ? $item->teacher->name() : null
            ];
          });
        });
  }

  private function getDefaultRoom(Teacher $teacher, Date $firstDate, Date $lastDate = null, $number) {
    return $this->lessonRepository
        ->queryForTeacher($teacher, $firstDate, $lastDate, $firstDate->dayOfWeek, $number)
        ->orderBy('date')
        ->orderBy('number')
        ->take(1)
        ->pluck('room_id')
        ->first();
  }

  private function getFirstChangedDate(Date $oldLastDate, Date $lastDate = null) {
    if (!$lastDate) {
      return null;
    }

    if ($lastDate < $oldLastDate) {
      // New last date is before the old one: Remove all lessons starting from the following week
      return $lastDate->next($oldLastDate->dayOfWeek);
    }
    if ($lastDate > $oldLastDate) {
      // New last date is after the old one: Add lessons starting on the first that is not an offday
      $firstAdded = $oldLastDate->copy();
      do {
        $firstAdded = $firstAdded->addWeek();
      } while ($firstAdded <= $lastDate && $this->offdayRepository->queryInRange($firstAdded)->exists());

      return ($firstAdded <= $lastDate) ? $firstAdded : null;
    }

    return null;
  }

  public function getMappedForTeacher(Teacher $teacher = null, Date $start, Date $end = null) {
    $query = $this->courseRepository->query($teacher, $start, $end)
        ->with('teacher:lastname,firstname');

    return $this->courseRepository->addParticipants($query)
        ->get()
        ->map(function(Course $course) {
          $first = new Lesson(['date' => Date::createFromFormat('Y-m-d', $course->first), 'number' => $course->number]);
          $this->configService->setTime($first);

          return [
              'id'          => $course->id,
              'name'        => $course->name,
              'first'       => $course->first,
              'last'        => $course->last !== $course->first ? $course->last : null,
              'time'        => $first->time,
              'teacher'     => $course->teacher->first()->name(),
              'students'    => $course->participants,
              'maxstudents' => $course->maxstudents
          ];
        });
  }

  public function getMappedObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start, Date $end = null) {
    return $this->courseRepository->queryObligatory($group, $teacher, $subject, $start, $end)
        ->with('teacher:lastname,firstname', 'groups:name')
        ->get()
        ->map(function(Course $course) {
          $first = new Lesson(['date' => Date::createFromFormat('Y-m-d', $course->first), 'number' => $course->number]);
          $this->configService->setTime($first);

          return [
              'id'      => $course->id,
              'name'    => $course->name,
              'first'   => $course->first,
              'last'    => $course->last !== $course->first ? $course->last : null,
              'time'    => $first->time,
              'teacher' => $course->teacher->first()->name(),
              'groups'  => $course->groups->pluck('name')
          ];
        });
  }

  public function getMappedForStudent(Student $student, Teacher $teacher = null, Date $start, Date $end = null) {
    $query = $this->courseRepository->queryAvailable($student, $teacher, $start, $end);

    return $this->courseRepository->addParticipants($query)
        ->get()
        ->map(function(Course $course) {
          $first = new Lesson(['date' => Date::createFromFormat('Y-m-d', $course->date), 'number' => $course->number]);
          $this->configService->setTime($first);
          $teacher = $course->teacher()->with('subjects')->first();

          return [
              'id'          => $course->id,
              'name'        => $course->name,
              'description' => $course->description,
              'first'       => $course->date,
              'last'        => $course->last !== $course->date ? $course->last : null,
              'time'        => $first->time,
              'teacher'     => [
                  'name'     => $teacher->name(),
                  'image'    => $teacher->image ? url($teacher->image) : null,
                  'info'     => $teacher->info,
                  'subjects' => $teacher->subjects->implode('name', ', ')
              ],
              'students'    => $course->participants,
              'maxstudents' => $course->maxstudents
          ];
        });
  }
}
