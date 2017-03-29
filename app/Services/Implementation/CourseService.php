<?php

namespace App\Services\Implementation;

use App\Exceptions\CourseException;
use App\Helpers\DateRange;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Offday;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\OffdayRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use App\Specifications\ObligatorySpecification;
use Carbon\Carbon;

class CourseService implements \App\Services\CourseService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var  GroupRepository */
  private $groupRepository;

  /** @var  OffdayRepository */
  private $offdayRepository;

  function __construct(ConfigService $configService, LessonService $lessonService, RegistrationService $registrationService,
      GroupRepository $groupRepository, OffdayRepository $offdayRepository) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->registrationService = $registrationService;
    $this->groupRepository = $groupRepository;
    $this->offdayRepository = $offdayRepository;
  }

  public function validateDates(Carbon $firstDate, Carbon $lastDate = null) {
    // Check if last date is before first date
    if (!is_null($lastDate) && $lastDate < $firstDate) {
      throw new CourseException(CourseException::INVALID_END_DATE);
    }

    // Check if both start and end date are within the school year
    $yearStart = $this->configService->getAsDate('year.start');
    $yearEnd = $this->configService->getAsDate('year.end');
    if (!$firstDate->between($yearStart, $yearEnd) || (!is_null($lastDate) && !$lastDate->between($yearStart, $yearEnd))) {
      throw new CourseException(CourseException::DATE_NOT_IN_YEAR);
    }

    // Check if a course can still be created for the start date
    $createDay = $this->configService->getAsInt('course.create.day');
    if (is_null($createDay)) {
      $createDay = 1;
      $createWeek = 0;
    } else {
      $createWeek = $this->configService->getAsInt('course.create.week');
    }
    $today = Carbon::now()->startOfDay();

    if ($createWeek <= 0 && $today->diffInDays($firstDate, false) < $createDay) {
      // Allow creation up to given days before the date
      throw new CourseException(CourseException::CREATE_PERIOD_ENDED);
    }

    if ($createWeek > 0 && $today->modify(DateRange::getDay($createDay))->startOfWeek()->diffInWeeks($firstDate) < $createWeek) {
      // Allow creation up to given weeks before the date, in relation to the start of the week for the given day of week
      throw new CourseException(CourseException::CREATE_PERIOD_ENDED);
    }

    // Check if first course day is offday
    if ($this->offdayRepository->inRange($firstDate)->exists()) {
      throw new CourseException(CourseException::OFFDAY);
    }
  }

  public function coursePossible(Teacher $teacher, Carbon $firstDate, Carbon $lastDate = null, array $numbers) {
    if ($this->lessonService->forTeacher($teacher, $firstDate, $lastDate, $firstDate->dayOfWeek, $numbers, false, true)->exists()) {
      throw new CourseException(CourseException::EXISTS);
    }
  }

  public function obligatoryPossible(array $groups, Carbon $firstDate, Carbon $lastDate = null, array $numbers) {
    if ($this->lessonService->forGroups($groups, $firstDate, $lastDate, $firstDate->dayOfWeek, $numbers)->exists()) {
      throw new CourseException(CourseException::OBLIGATORY_EXISTS);
    }
  }

  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher) {
    $firstDate = $spec->getFirstDate();
    $lastDate = $spec->getLastDate();
    $dayOfWeek = $firstDate->dayOfWeek;

    $numbers = $this->lessonService->getLessonsForDay($dayOfWeek, $spec->getFirstLesson(), $spec->getLastLesson());
    $this->validateDates($firstDate, $lastDate);

    // Check for existing courses on one of the lessons
    $this->coursePossible($teacher, $firstDate, $lastDate, $numbers);

    if ($spec instanceof ObligatorySpecification) {
      $this->obligatoryPossible($spec->getGroups(), $firstDate, $lastDate, $numbers);
    }

    $course = $spec->populateCourse();
    if (!$course->save()) {
      throw new CourseException(CourseException::SAVE_FAILED);
    }

    // Load all of the teachers lessons for the given slot
    $lessons = $this->lessonService->forTeacher($teacher, $firstDate, $lastDate, $dayOfWeek, $numbers, true)->get(['date', 'number']);

    if (!$lessons->contains($this->lessonMatcher($firstDate, null, false))) {
      // Teacher has no lesson at the first course date, so create them for all days except offdays
      $offdays = $this->offdayRepository->inRange($firstDate, $lastDate, $dayOfWeek)->get(['date']);

      for ($d = $firstDate; $d <= $lastDate; $d = $d->copy()->addWeek()) {
        if ($offdays->contains($this->offdayMatcher($d))) {
          continue;
        }

        foreach ($numbers as $n) {
          if (!$lessons->contains($this->lessonMatcher($d, $n))) {
            $lessons->push(new Lesson(['teacher' => $teacher, 'date' => $d, 'number' => $n]));
          }
        }
      }
    }

    $course->lessons()->saveMany($lessons->filter(function(Lesson $lesson) {
      return empty($lesson->cancelled);
    }));

    if ($spec instanceof ObligatorySpecification) {
      $this->groupRepository->queryById($spec->getGroups())->get()
          ->each(function(Group $group) use ($course) {
            $this->registrationService->registerGroupForCourse($course, $group);
          });
    }
  }

  public function editCourse(EditCourseSpecification $course) {
    // TODO Implement
  }

  public function removeCourse(Course $course) {
    // TODO Implement
  }

  private function lessonMatcher(Carbon $date, $number = null, $cancelled = null) {
    return function(Lesson $lesson) use ($date, $number, $cancelled) {
      return $lesson->date == $date && (is_null($number) || $lesson->number === $number)
          && (is_null($cancelled) || $lesson->cancelled === $cancelled);
    };
  }

  private function offdayMatcher(Carbon $date) {
    return function(Offday $offday) use ($date) {
      return $offday->date == $date;
    };
  }

}