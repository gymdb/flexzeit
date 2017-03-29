<?php

namespace Tests\Unit;

use App\Exceptions\CourseException;
use App\Models\Course;
use App\Models\Group;
use App\Models\Teacher;
use App\Repositories\CourseRepository;
use App\Repositories\GroupRepository;
use App\Repositories\OffdayRepository;
use App\Services\Implementation\CourseService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\ObligatorySpecification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Tests\Traits\MockConfigTrait;
use Tests\Traits\MockDbTrait;
use Tests\Traits\MockLessonsTrait;
use Tests\Traits\MockOffdaysTrait;
use VladaHejda\AssertException;

/**
 * Test the CourseService class
 *
 * @package Tests\Unit
 */
class CourseServiceTest extends TestCase {

  use MockConfigTrait;
  use MockDbTrait;
  use MockLessonsTrait;
  use MockOffdaysTrait;
  use AssertException;

  /**
   * @var CourseService
   */
  private $courseService;

  // *******************************
  // Methods for setting up the test
  // *******************************

  /**
   * Create the CourseService
   *
   * @param bool $partialMock Mock the getLessonsForDay and validateDates methods (for testing only createCourse)
   */
  private function createService($partialMock = false) {
    $this->mock(['repo'    => CourseRepository::class, 'groups' => GroupRepository::class, 'offdays' => OffdayRepository::class,
                 'lessons' => LessonService::class, 'registrations' => RegistrationService::class]);
    $this->mockConfig([]);
    $this->courseService = $partialMock
        ? \Mockery::mock(CourseService::class . '[validateDates,coursePossible,obligatoryPossible]',
            [$this->getMocked('configService'), $this->getMocked('lessons'), $this->getMocked('registrations'),
                $this->getMocked('groups'), $this->getMocked('offdays')])
        : $this->app->make(CourseService::class);
  }

  /**
   * Mock the getLessonsForDay and validateDates methods of the CourseService, such that the given parameters will be accepted as correct
   *
   * @param Teacher $teacher
   * @param Carbon $startDate
   * @param Carbon|null $endDate
   * @param int $firstLesson
   * @param int|null $lastLesson
   * @param array $result The result getLessonsForDay should return
   * @param int $count Number of expected calls to each of the methods
   */
  private function mockValidation(Teacher $teacher, Carbon $startDate, Carbon $endDate = null, $firstLesson = 1, $lastLesson = null, $result = [1], $count = 1) {
    $this->createService(true);

    $this->shouldReceive('lessons', 'getLessonsForDay')
        ->with($startDate->dayOfWeek, $firstLesson, $lastLesson)
        ->between($count, $count)
        ->andReturn($result);

    $this->courseService->shouldReceive('validateDates')
        ->andReturnUsing(function($firstDate, $lastDate) use ($startDate, $endDate) {
          $this->assertEquals($startDate, $firstDate);
          $this->assertEquals($endDate, $lastDate);
        })
        ->between($count, $count);

    $this->courseService->shouldReceive('coursePossible')
        ->andReturnUsing(function($actualTeacher, $firstDate, $lastDate, $numbers) use ($teacher, $startDate, $endDate, $result) {
          $this->assertSame($teacher, $actualTeacher);
          $this->assertEquals($startDate, $firstDate);
          $this->assertEquals($endDate, $lastDate);
          $this->assertEquals($result, $numbers);
        })
        ->between($count, $count);
  }

  private function mockObligatoryPossible(array $groups, Carbon $startDate, Carbon $endDate = null, array $numbers = [1], $count = 1) {
    $this->courseService->shouldReceive('obligatoryPossible')
        ->andReturnUsing(function($actualGroups, $firstDate, $lastDate, $actualNumbers) use ($groups, $startDate, $endDate, $numbers) {
          $this->assertEquals($groups, $actualGroups);
          $this->assertEquals($startDate, $firstDate);
          $this->assertEquals($endDate, $lastDate);
          $this->assertEquals($numbers, $actualNumbers);
        })
        ->between($count, $count);
  }

  /**
   * Create a mock of a CourseSpecification
   *
   * @param Carbon $firstDate
   * @param Carbon|null $lastDate
   * @param Course|null $courseMock Optional course to be returned from the specification
   * @param int[]|null $groups
   * @return CreateCourseSpecification|\Mockery\MockInterface
   */
  private function mockSpec(Carbon $firstDate, Carbon $lastDate = null, Course $courseMock = null, array $groups = null) {
    $class = CreateCourseSpecification::class;
    if ($groups) {
      $class .= ',' . ObligatorySpecification::class;
    }
    $spec = \Mockery::mock($class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $spec->shouldReceive([
        'getFirstDate'   => $firstDate->copy(),
        'getLastDate'    => $lastDate ? $lastDate->copy() : null,
        'getFirstLesson' => 1,
        'getLastLesson'  => null
    ]);

    if ($groups) {
      /** @noinspection PhpMethodParametersCountMismatchInspection */
      $spec->shouldReceive(['getGroups' => $groups, 'getSubject' => 4]);
    }

    if ($courseMock) {
      /** @noinspection PhpMethodParametersCountMismatchInspection */
      $spec->shouldReceive('populateCourse')->withNoArgs()->andReturn($courseMock);
    }
    return $spec;
  }

  // ***********************
  // Tests for validateDates
  // ***********************

  public function testInvalidEndDate() {
    $this->createService();
    $this->assertException(function() {
      $date = Carbon::now()->startOfDay()->addMonth();
      $this->courseService->validateDates($date, $date->copy()->addDay(-1));
    }, CourseException::class, CourseException::INVALID_END_DATE);
  }

  public function testDateNotInYear() {
    $this->mockConfig(['year.start' => Carbon::createFromDate(2017, 4, 1), 'year.end' => Carbon::createFromDate(2017, 5, 31)]);
    $this->createService();

    $dates = [
        [Carbon::createFromDate(2017, 3, 31), null],
        [Carbon::createFromDate(2017, 6, 1), null],
        [Carbon::createFromDate(2017, 5, 20), Carbon::createFromDate(2017, 6, 1)]
    ];

    foreach ($dates as $date) {
      $this->assertException(function() use ($date) {
        $this->courseService->validateDates($date[0], $date[1]);
      }, CourseException::class, CourseException::DATE_NOT_IN_YEAR);
    }
  }

  public function testStartNotAllowed() {
    $this->mockConfig(['year.start' => Carbon::createFromDate(2017, 2, 1), 'year.end' => Carbon::now()->startOfDay()->addMonths(3)]);
    $this->createService();

    // Try creating courses if creation is always allowed up to $i days before the course
    for ($i = -2; $i < 10; $i++) {
      $this->mockConfig(['course.create.week' => 0, 'course.create.day' => max(1, $i + 1)]);
      $date = Carbon::now()->startOfDay()->addDay($i);
      $this->assertException(function() use ($date) {
        $this->courseService->validateDates($date, null);
      }, CourseException::class, CourseException::CREATE_PERIOD_ENDED);
    }

    // Try creating courses if creation is always allowed until today's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Carbon::now()->startOfDay()->dayOfWeek]);
      $date = Carbon::now()->startOfDay()->endOfWeek()->addWeek($i - 1);
      $this->assertException(function() use ($date) {
        $this->courseService->validateDates($date, null);
      }, CourseException::class, CourseException::CREATE_PERIOD_ENDED);
    }

    // Try creating courses if creation is always allowed until yesterday's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Carbon::now()->startOfDay()->addDays(-1)->dayOfWeek]);
      $date = Carbon::now()->startOfDay()->endOfWeek()->addWeek($i);
      $this->assertException(function() use ($date) {
        $this->courseService->validateDates($date, null);
      }, CourseException::class, CourseException::CREATE_PERIOD_ENDED);
    }
  }

  public function testStartOffday() {
    $this->mockConfig(['year.start' => Carbon::createFromDate(2017, 2, 1), 'year.end' => Carbon::now()->startOfDay()->addMonths(3)]);
    $this->createService();

    // Mock a few offdays for the whole school
    $dates = [Carbon::now()->startOfDay()->next(Carbon::MONDAY), Carbon::now()->startOfDay()->next(Carbon::WEDNESDAY)];
    $this->mockOffdaysInRange($dates);

    foreach ($dates as $date) {
      $this->assertException(function() use ($date) {
        $this->courseService->validateDates($date, null);
      }, CourseException::class, CourseException::OFFDAY);
    }
  }

  public function testValidateDates() {
    $this->mockConfig(['year.start' => Carbon::createFromDate(2017, 2, 1), 'year.end' => Carbon::now()->startOfDay()->addMonths(3)]);
    $this->createService();

    $dates = [Carbon::now()->startOfDay()->next(Carbon::MONDAY)->addWeeks(2), Carbon::now()->startOfDay()->next(Carbon::WEDNESDAY)->addWeek()];
    $this->mockOffdaysInRange($dates);

    // Try creating courses if creation is always allowed up to $i days before the course
    for ($i = 1; $i < 10; $i++) {
      $this->mockConfig(['course.create.week' => 0, 'course.create.day' => $i]);
      $date = Carbon::now()->startOfDay()->addDay($i - 3);
      $this->courseService->validateDates($date, $date->addWeeks(11));
    }

    // Try creating courses if creation is always allowed until today's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Carbon::now()->startOfDay()->dayOfWeek]);
      $date = Carbon::now()->startOfDay()->startOfWeek()->addWeek($i);
      $this->courseService->validateDates($date, $date->addWeeks(12 - $i));
    }

    // Try creating courses if creation is always allowed until yesterday's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Carbon::now()->startOfDay()->addDays(-1)->dayOfWeek]);
      $date = Carbon::now()->startOfDay()->startOfWeek()->addWeek($i + 1);
      $this->courseService->validateDates($date, $date->addWeeks(12 - $i));
    }
  }

  // **************************
  // Tests for existing courses
  // **************************

  public function testCourseExists() {
    $startDate = Carbon::now()->startOfDay()->addWeek();
    $endDate = $startDate->copy()->addMonth();
    $teacher = $this->mockModel(Teacher::class);

    $this->createService();
    $this->mockLessons(['forTeacher' => $teacher], [['date' => $startDate->copy()->addWeeks(3), 'withCourse' => true]]);

    $this->assertException(function() use ($teacher, $startDate, $endDate) {
      $this->courseService->coursePossible($teacher, $startDate, $endDate, [1]);
    }, CourseException::class, CourseException::EXISTS);
  }

  public function testObligatoryExists() {
    $startDate = Carbon::now()->startOfDay()->addWeek();
    $endDate = $startDate->copy()->addMonth();

    $this->createService();
    $this->mockLessons(['forGroups' => [1, 3, 7]], [['date' => $startDate->copy()->addWeeks(3)]]);

    $this->assertException(function() use ($startDate, $endDate) {
      $this->courseService->obligatoryPossible([1, 3, 7], $startDate, $endDate, [1]);
    }, CourseException::class, CourseException::OBLIGATORY_EXISTS);
  }

  // ******************************
  // Failing tests for createCourse
  // ******************************

  public function testSaveFailed() {
    $startDate = Carbon::now()->startOfDay()->addWeek();

    /** @var Course $course */
    $course = $this->mockModel(Course::class);
    $teacher = $this->mockModel(Teacher::class);

    $this->mockValidation($teacher, $startDate);
    $this->mockLessons();
    $course->shouldReceive(['save' => false])->between(1, 1);

    $courseSpec = $this->mockSpec($startDate, null, $course);

    $this->assertException(function() use ($courseSpec, $teacher) {
      $this->courseService->createCourse($courseSpec, $teacher);
    }, CourseException::class, CourseException::SAVE_FAILED);
  }

  // *********************************
  // Successful tests for createCourse
  // *********************************

  private function create(Carbon $startDate, Carbon $endDate = null, array $numbers, array $lessons, array $offdays, array $expected, array $groups = null) {
    /** @var Course $course */
    $course = $this->mockModel(Course::class);
    $teacher = $this->mockModel(Teacher::class);

    $this->mockValidation($teacher, $startDate, $endDate, 1, null, $numbers);
    $this->mockOffdaysInRange($offdays);

    $this->mockLessons(['forTeacher' => $teacher], $lessons);

    $relation = \Mockery::mock(HasMany::class);
    $course->shouldReceive(['save' => true])->between(1, 1);
    $course->shouldReceive('lessons')->andReturn($relation);

    if ($groups) {
      $this->mockObligatoryPossible($groups, $startDate, $endDate, $numbers);
      $groupMocks = [];
      foreach ($groups as $group) {
        $groupMocks[] = $mock = $this->mockModel(Group::class, ['id' => $group]);
        $this->shouldReceive('registrations', 'registerGroupForCourse')
            ->with($course, $mock)
            ->between(1, 1);
      }
      $this->shouldReceive('groups', 'queryById')
          ->with($groups)
          ->andReturn($this->mockResult(collect($groupMocks)))
          ->between(1, 1);
    }

    $courseSpec = $this->mockSpec($startDate, $endDate, $course, $groups);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $relation->shouldReceive('saveMany')
        ->between(1, 1)
        ->with(\Mockery::on(function(Collection $lessons) use ($expected) {
          $this->assertEquals(array_sum(array_map('count', $expected)), $lessons->count(), 'Lesson count is incorrect');

          foreach ($lessons as $lesson) {
            if (!isset($expected[$lesson->number]) || !in_array($lesson->date, $expected[$lesson->number])) {
              $this->fail('Did not expect lesson ' . $lesson->number . ' at ' . $lesson->date);
            }
          }

          return true;
        }));

    $this->courseService->createCourse($courseSpec, $teacher);
  }

  public function testCreateSingleDay() {
    $startDate = Carbon::now()->startOfDay()->addWeek();

    $d1 = $startDate->copy()->addWeek();
    $d2 = $startDate->copy()->addWeeks(2);
    $d4 = $startDate->copy()->addWeeks(4);

    $lessons = [$startDate, $d1, $d4];
    $offdays = [$d2];
    $expected = [1 => [$startDate], 2 => [$startDate]];

    $this->create($startDate, null, [1, 2], $lessons, $offdays, $expected);
  }

  public function testCreateRepeated() {
    $startDate = Carbon::now()->startOfDay()->addWeek();
    $endDate = $startDate->copy()->addMonth();

    $d1 = $startDate->copy()->addWeek();
    $d2 = $startDate->copy()->addWeeks(2);
    $d4 = $startDate->copy()->addWeeks(4);

    $lessons = [$startDate, $d4, ['date' => $d2, 'number' => 1], ['date' => $d2, 'number' => 2, 'cancelled' => true]];
    $offdays = [$d1];
    $expected = [1 => [$startDate, $d2, $d4], 2 => [$startDate, $d4]];

    $this->create($startDate, $endDate, [1, 2], $lessons, $offdays, $expected);
  }

  public function testCreateRepeatedAdditional() {
    $startDate = Carbon::now()->startOfDay()->addWeek();
    $endDate = $startDate->copy()->addMonth();

    $d1 = $startDate->copy()->addWeek();
    $d2 = $startDate->copy()->addWeeks(2);
    $d3 = $startDate->copy()->addWeeks(3);
    $d4 = $startDate->copy()->addWeeks(4);

    $dates = [$d2, ['date' => $startDate, 'number' => 2, 'cancelled' => true]];
    $offdays = [$d1];
    $expected = [1 => [$startDate, $d2, $d3, $d4], 2 => [$d2, $d3, $d4]];

    $this->create($startDate, $endDate, [1, 2], $dates, $offdays, $expected);
  }

  public function testCreateObligatory() {
    $startDate = Carbon::now()->startOfDay()->addWeek();
    $endDate = $startDate->copy()->addMonth();

    $d1 = $startDate->copy()->addWeek();
    $d2 = $startDate->copy()->addWeeks(2);
    $d4 = $startDate->copy()->addWeeks(4);

    $lessons = [$startDate, $d4, ['date' => $d2, 'number' => 1], ['date' => $d2, 'number' => 2, 'cancelled' => true]];
    $offdays = [$d1];
    $expected = [1 => [$startDate, $d2, $d4], 2 => [$startDate, $d4]];

    $this->create($startDate, $endDate, [1, 2], $lessons, $offdays, $expected, [1, 3, 7]);
  }

}
