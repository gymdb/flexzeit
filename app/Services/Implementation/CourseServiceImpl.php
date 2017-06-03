<?php

namespace App\Services\Implementation;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\RegistrationService;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use App\Specifications\ObligatorySpecification;
use App\Validators\DateValidator;
use Illuminate\Database\Eloquent\Builder;

class CourseServiceImpl implements CourseService {

  /** @var ConfigService */
  private $configService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var  GroupRepository */
  private $groupRepository;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var  OffdayRepository */
  private $offdayRepository;

  /** @var  DateValidator */
  private $dateValidator;

  function __construct(ConfigService $configService, RegistrationService $registrationService, GroupRepository $groupRepository,
      LessonRepository $lessonRepository, OffdayRepository $offdayRepository, DateValidator $dateValidator) {
    $this->configService = $configService;
    $this->registrationService = $registrationService;
    $this->groupRepository = $groupRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
    $this->dateValidator = $dateValidator;
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

  public function getFirstCreateDate() {
    return max($this->configService->getAsDate('year.start'), $this->dateValidator->getDateBound('course.create'));
  }

  public function getLastCreateDate() {
    return $this->configService->getAsDate('year.end');
  }

  public function getMinYear() {
    return $this->configService->getAsInt('year.min', 1);
  }

  public function getMaxYear() {
    return $this->configService->getAsInt('year.max', 1);
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

    if (!$lessons->contains($this->matcher($firstDate, null, false))) {
      // Teacher has no lesson at the first course date, so create them for all days except offdays
      $offdays = $this->offdayRepository->inRange($firstDate, $lastDate, $dayOfWeek)->get(['date']);

      for ($d = $firstDate; $d <= ($lastDate ?: $firstDate); $d = $d->copy()->addWeek()) {
        if ($offdays->contains($this->matcher($d))) {
          continue;
        }

        foreach ($numbers as $n) {
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

  private function matcher(Date $date, $number = null, $cancelled = null) {
    return function($item) use ($date, $number, $cancelled) {
      return $item->date == $date
          && (is_null($number) || $item->number === $number)
          && (is_null($cancelled) || $item->cancelled === $cancelled);
    };
  }
}