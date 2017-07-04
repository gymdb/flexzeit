<?php

namespace App\Services\Implementation;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Repositories\Eloquent\CourseRepository;
use App\Repositories\Eloquent\RepositoryHelper;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Services\CourseService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use App\Specifications\ObligatorySpecification;
use Illuminate\Database\Eloquent\Builder;

class CourseServiceImpl implements CourseService {

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var CourseRepository */
  private $courseRepository;

  /** @var  GroupRepository */
  private $groupRepository;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var  OffdayRepository */
  private $offdayRepository;

  function __construct(RegistrationService $registrationService, LessonService $lessonService, CourseRepository $courseRepository,
      GroupRepository $groupRepository, LessonRepository $lessonRepository, OffdayRepository $offdayRepository) {
    $this->lessonService = $lessonService;
    $this->registrationService = $registrationService;
    $this->courseRepository = $courseRepository;
    $this->groupRepository = $groupRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
  }

  public function coursePossible(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    if ($this->buildLessonsWithCourse($teacher, $firstDate, $lastDate, $numbers)->exists()) {
      throw new CourseException(CourseException::EXISTS);
    }
  }

  public function obligatoryPossible(Builder $groups, Date $firstDate, Date $lastDate = null, $numbers) {
    if ($this->lessonRepository->forGroups($groups, $firstDate, $lastDate, $firstDate->dayOfWeek, $numbers)->exists()) {
      throw new CourseException(CourseException::OBLIGATORY_EXISTS);
    }
  }

  public function getLessonsWithCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    return $this->buildLessonsWithCourse($teacher, $firstDate, $lastDate, $numbers)
        ->orderBy('date')
        ->orderBy('number')
        ->with('course')
        ->get(['date', 'number', 'course_id'])
        ->map(function(Lesson $lesson) {
          return [
              'date'   => (string)$lesson->date,
              'number' => $lesson->number,
              'course' => $lesson->course->name
          ];
        });
  }

  public function getLessonsForCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    return $this->buildLessonsForCourse($teacher, $firstDate, $lastDate, $numbers)
        ->sortBy('date')
        ->map(function(Lesson $lesson) {
          return [
              'date'   => (string)$lesson->date,
              'number' => $lesson->number,
              'room'   => $lesson->room
          ];
        })->values();
  }

  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher) {
    $firstDate = $spec->getFirstDate();
    $lastDate = $spec->getLastDate();
    $numbers = $spec->getLessonNumber();

    // Check for existing courses on one of the lessons
    $this->coursePossible($teacher, $firstDate, $lastDate, $numbers);

    $groups = null;
    if ($spec instanceof ObligatorySpecification) {
      // Check for existing courses for one of the groups
      $groups = $this->groupRepository->queryById($spec->getGroups());
      $this->obligatoryPossible($groups, $firstDate, $lastDate, $numbers);
    }

    $course = $spec->populateCourse();
    if (!$course->save()) {
      throw new CourseException(CourseException::SAVE_FAILED);
    }

    // Load all of the teachers lessons for the given slot
    $lessons = $this->buildLessonsForCourse($teacher, $firstDate, $lastDate, $numbers);
    $lessons->each(function(Lesson $lesson) use ($course) {
      if ($lesson->exists) {
        $lesson->course()->associate($course);
        $lesson->save();
      } else {
        $course->lessons()->save($lesson);
      }
    });

    if ($spec instanceof ObligatorySpecification) {
      $groups->get()->each(function(Group $group) use ($course) {
        $this->registrationService->registerGroupForCourse($course, $group);
      });
    }

    return $course;
  }

  public function editCourse(EditCourseSpecification $course) {
    // TODO Implement
  }

  public function removeCourse(Course $course) {
    // TODO Implement
  }

  private function buildLessonsWithCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    return $this->lessonRepository->forTeacher($teacher, $firstDate, $lastDate, $firstDate->dayOfWeek, $numbers, false, true);
  }

  private function buildLessonsForCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers) {
    if (is_scalar($numbers)) {
      $numbers = [$numbers];
    }
    $dayOfWeek = $firstDate->dayOfWeek;

    $lessons = $this->lessonRepository
        ->forTeacher($teacher, $firstDate, $lastDate, $dayOfWeek, $numbers, true)
        ->get(['id', 'date', 'number', 'cancelled', 'room']);

    if (!$lessons->contains(RepositoryHelper::matcher($firstDate, null, false))) {
      // Teacher has no lesson at the first course date, so create them for all days except offdays
      $offdays = $this->offdayRepository->inRange($firstDate, $lastDate, $dayOfWeek, $numbers)->get(['date']);

      for ($d = $firstDate; $d <= ($lastDate ?: $firstDate); $d = $d->copy()->addWeek()) {
        foreach ($numbers as $n) {
          if ($offdays->contains(RepositoryHelper::matcher($d, $n))) {
            continue;
          }
          if (!$lessons->contains(RepositoryHelper::matcher($d, $n))) {
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

  public function getMappedForTeacher(Teacher $teacher, Date $start, Date $end = null) {
    return $this->courseRepository->forTeacher($teacher, $start, $end)
        ->get()
        ->map(function(Course $course) {
          $first = new Lesson(['date' => Date::createFromFormat('Y-m-d', $course->first), 'number' => $course->number]);
          $this->lessonService->setTime($first);

          return [
              'id'      => $course->id,
              'name'    => $course->name,
              'first'   => $course->first,
              'last'    => $course->last !== $course->first ? $course->last : null,
              'time'    => $first->time,
              'teacher' => $course->teacher->first()->name()
          ];
        });
  }

}