<?php

namespace App\Services\Implementation;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Mail\ObligatoryCreated;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\CourseRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\StudentRepository;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\RegistrationService;
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

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var  OffdayRepository */
  private $offdayRepository;

  /** @var StudentRepository */
  private $studentRepository;

  function __construct(ConfigService $configService, RegistrationService $registrationService, CourseRepository $courseRepository,
      GroupRepository $groupRepository, LessonRepository $lessonRepository, OffdayRepository $offdayRepository, StudentRepository $studentRepository) {
    $this->configService = $configService;
    $this->registrationService = $registrationService;
    $this->courseRepository = $courseRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
    $this->studentRepository = $studentRepository;
  }

  public function coursePossible(Teacher $teacher, Date $firstDate, Date $lastDate = null, $number) {
    if ($this->lessonRepository->queryForTeacher($teacher, $firstDate, $lastDate, $firstDate->dayOfWeek, $number, false, true)->exists()) {
      throw new CourseException(CourseException::EXISTS);
    }
  }

  public function obligatoryPossible(array $groups, Date $firstDate, Date $lastDate = null, $number, Course $exclude = null) {
    if ($this->lessonRepository->queryForGroups($groups, $firstDate, $lastDate, $firstDate->dayOfWeek, $number, $exclude)->exists()) {
      throw new CourseException(CourseException::OBLIGATORY_EXISTS);
    }
  }

  public function getLessonsForCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    return $this->buildLessonsForCourse($teacher, $firstDate, $lastDate, $numbers)
        ->sortBy('date')
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);

          return [
              'date' => $lesson->date->toDateString(),
              'time' => $lesson->time,
              'room' => $lesson->room
          ];
        })->values();
  }

  public function getLessonsWithObligatory(array $groups, Date $firstDate, Date $lastDate = null, $number, Course $exclude = null) {
    return $this->lessonRepository->queryForGroups($groups, $firstDate, $lastDate, $firstDate->dayOfWeek, $number, $exclude)
        ->get(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.course_id'])
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);

          return [
              'date'   => $lesson->date->toDateString(),
              'time'   => $lesson->time,
              'course' => [
                  'name'   => $lesson->course->name,
                  'groups' => $lesson->course->groups->pluck('name')
              ]
          ];
        })->values();
  }

  private function assignCourse(Collection $lessons, Course $course) {
    $lessons->each(function(Lesson $lesson) use ($course) {
      if ($lesson->exists) {
        $lesson->course()->associate($course);
        $lesson->save();
      } else {
        $course->lessons()->save($lesson);
      }
    });
  }

  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher) {
    $firstDate = $spec->getFirstDate();
    $lastDate = $spec->getLastDate();
    $number = $spec->getLessonNumber();

    // Check for existing courses on one of the lessons
    $this->coursePossible($teacher, $firstDate, $lastDate, $number);

    $groups = null;
    if ($spec instanceof ObligatorySpecification) {
      // Check for existing courses for one of the groups
      $groups = $spec->getGroups();
      $this->obligatoryPossible($spec->getGroups(), $firstDate, $lastDate, $number);
    }

    $course = $spec->populateCourse();
    if (!$course->save()) {
      throw new CourseException(CourseException::SAVE_FAILED);
    }

    // Load all of the teachers lessons for the given slot
    $lessons = $this->buildLessonsForCourse($teacher, $firstDate, $lastDate, $number);
    $this->assignCourse($lessons, $course);

    if ($spec instanceof ObligatorySpecification) {
      $course->groups()->sync($groups);
      $course->subject()->associate(Subject::find($spec->getSubject()));
      $course->save();

      $students = $this->studentRepository->queryForGroups($groups)->pluck('id');
      $this->registrationService->registerStudentsForCourse($course, $students);

      Mail::to($this->configService->getNotificationRecipients())
          ->send(new ObligatoryCreated($teacher, $course, $lessons->sortBy('date')));
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
    $firstChanged = $this->getFirstChanged($oldLastDate, $lastDate);
    if ($firstChanged) {
      // Check if changing course duration is still possible
      if ($oldLastDate->copy()->addWeek() < $firstCreateDate) {
        throw new CourseException(CourseException::EDIT_PERIOD);
      }

      // Check for existing courses on one of the newly added lessons
      if ($firstChanged > $oldLastDate) {
        $this->coursePossible($teacher, $firstChanged, $lastDate, $number);
      }
    }

    // Load group changes
    $groups = $groupsChanged = null;
    if ($spec instanceof ObligatorySpecification) {
      $groups = $spec->getGroups();

      // Check for existing courses for one of the groups
      $this->obligatoryPossible($groups, $firstDate, $lastDate, $number, $course);

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
        // Load already registered students

        // Add lessons
        $lessons = $this->buildLessonsForCourse($teacher, $firstChanged, $lastDate, $number);
        $this->assignCourse($lessons, $course);

        // Register students for added lessons
        $students = $course->registrations()->distinct()->pluck('student_id');
        $this->registrationService->registerStudentsForCourse($course, $students, $firstChanged);
      }
    }

    if ($spec instanceof ObligatorySpecification) {
      // Set new subject
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
          $this->registrationService->registerStudentsForCourse($course, $addedStudents);
        }
      }
    }

    return $course;
  }

  public function getFirstChanged(Date $oldLastDate, Date $lastDate = null) {
    if (!$lastDate) {
      return null;
    }

    if ($lastDate < $oldLastDate) {
      return $lastDate->addWeek();
    }
    if ($lastDate > $oldLastDate) {
      $firstAdded = $oldLastDate->copy();
      do {
        $firstAdded = $firstAdded->addWeek();
      } while ($firstAdded <= $lastDate && $this->offdayRepository->queryInRange($firstAdded)->exists());

      return ($firstAdded <= $lastDate) ? $firstAdded : null;
    }

    return null;
  }

  public function removeCourse(Course $course) {
    if (!$course->firstLesson()->date->isFuture()) {
      throw new CourseException(CourseException::DELETE_PERIOD);
    }

    $this->registrationService->unregisterAllFromCourse($course);
    $course->lessons()->update(['course_id' => null]);
    $course->groups()->sync([]);
    $course->delete();
  }

  private function buildLessonsForCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    if (is_scalar($numbers)) {
      $numbers = [$numbers];
    }
    $dayOfWeek = $firstDate->dayOfWeek;

    $lessons = $this->lessonRepository
        ->queryForTeacher($teacher, $firstDate, $lastDate, $dayOfWeek, $numbers, true)
        ->get(['id', 'date', 'number', 'cancelled', 'room']);

    if (!$lessons->contains($this->matcher($firstDate, null, false))) {
      // Teacher has no lesson at the first course date, so create them for all days except offdays
      $offdays = $this->offdayRepository->queryInRange($firstDate, $lastDate, $dayOfWeek, $numbers)->get(['date', 'number']);

      for ($d = $firstDate; $d <= ($lastDate ?: $firstDate); $d = $d->copy()->addWeek()) {
        foreach ($numbers as $n) {
          if ($offdays->contains($this->matcher($d, $n))) {
            continue;
          }
          if (!$lessons->contains($this->matcher($d, $n))) {
            $lesson = new Lesson(['date' => $d, 'number' => $n, 'room' => '']);
            $lesson->teacher()->associate($teacher);
            $lessons->push($lesson);
          }
        }
      }
    }

    return $lessons->filter(function(Lesson $lesson) {
      return empty($lesson->cancelled);
    });
  }

  public function getMappedForTeacher(Teacher $teacher = null, Date $start, Date $end = null) {
    return $this->courseRepository->query($teacher, $start, $end)
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
              'students'    => $course->students()->count(),
              'maxstudents' => $course->maxstudents
          ];
        });
  }

  public function getMappedObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start, Date $end = null) {
    return $this->courseRepository->queryObligatory($group, $teacher, $subject, $start, $end)
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

  public function getMappedRemovedLessons(Course $course, Date $firstRemoved) {
    return $course->lessons()
        ->where('date', '>=', $firstRemoved)
        ->orderBy('date')
        ->orderBy('number')
        ->get(['date', 'number'])
        ->map(function(Lesson $lesson) {
          $this->configService->setTime($lesson);

          return [
              'date' => $lesson->date->toDateString(),
              'time' => $lesson->time
          ];
        })->values();
  }

}