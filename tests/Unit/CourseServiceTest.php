<?php

namespace Tests\Unit;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Services\Implementation\CourseService;
use App\Services\RegistrationService;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use App\Specifications\ObligatorySpecification;
use App\Validators\DateValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;
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
    $this->mockConfig([]);
    $this->mock(['registrations' => RegistrationService::class, 'groups' => GroupRepository::class,
                 'lessons'       => LessonRepository::class, 'offdays' => OffdayRepository::class]);
    $this->courseService = $partialMock
        ? Mockery::mock(CourseService::class . '[coursePossible,obligatoryPossible]',
            [$this->getMocked('configService'), $this->getMocked('registrations'), $this->getMocked('groups'),
                $this->getMocked('lessons'), $this->getMocked('offdays'), $this->app->make(DateValidator::class)])
        : $this->app->make(CourseService::class);
  }

  /**
   * Mock the coursePossible method
   *
   * @param Teacher $teacher
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int[] $numbers
   * @param int $count Number of expected calls to each of the methods
   */
  private function mockCoursePossible(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers = [1], $count = 1) {
    if (!$this->courseService) {
      $this->createService(true);
    }

    $this->courseService->shouldReceive('coursePossible')
        ->andReturnUsing(function($actualTeacher, $actualFirstDate, $actualLastDate, $actualNumbers) use ($teacher, $firstDate, $lastDate, $numbers) {
          $this->assertSame($teacher, $actualTeacher);
          $this->assertEquals($firstDate, $actualFirstDate);
          $this->assertEquals($lastDate, $actualLastDate);
          $this->assertEquals($numbers, $actualNumbers);
        })
        ->between($count, $count);
  }

  /**
   * Mock the obligatoryPossible method
   *
   * @param int[] $groups
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int[] $numbers
   * @param int $count Number of expected calls to each of the methods
   */
  private function mockObligatoryPossible(array $groups, Date $firstDate, Date $lastDate = null, array $numbers = [1], $count = 1) {
    if (!$this->courseService) {
      $this->createService(true);
    }

    $this->courseService->shouldReceive('obligatoryPossible')
        ->andReturnUsing(function($actualGroups, $actualFirstDate, $actualLastDate, $actualNumbers) use ($groups, $firstDate, $lastDate, $numbers) {
          $this->assertEquals($firstDate, $actualFirstDate);
          $this->assertEquals($lastDate, $actualLastDate);
          $this->assertEquals($numbers, $actualNumbers);
        })
        ->between($count, $count);
  }

  /**
   * Create a mock of a CreateCourseSpecification
   *
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int[] $numbers
   * @param Course|null $courseMock Optional course to be returned from the specification
   * @param int[]|null $groups
   * @return CreateCourseSpecification|\Mockery\MockInterface
   */
  private function mockCreateSpec(Date $firstDate, Date $lastDate = null, Course $courseMock = null, array $numbers = [1], array $groups = null) {
    $class = CreateCourseSpecification::class;
    if ($groups) {
      $class .= ',' . ObligatorySpecification::class;
    }
    $spec = Mockery::mock($class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $spec->shouldReceive([
        'getFirstDate'    => $firstDate->copy(),
        'getLastDate'     => $lastDate ? $lastDate->copy() : null,
        'getLessonNumber' => $numbers
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

  /**
   * Create a mock of an EditCourseSpecification
   *
   * @param int $id
   * @param Date|null $lastDate
   * @param Course|null $courseMock Optional course to be returned from the specification
   * @param int[]|null $groups
   * @return EditCourseSpecification|\Mockery\MockInterface
   */
  private function mockEditSpec($id, Date $lastDate = null, Course $courseMock = null, array $groups = null) {
    $class = EditCourseSpecification::class;
    if ($groups) {
      $class .= ',' . ObligatorySpecification::class;
    }
    $spec = Mockery::mock($class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $spec->shouldReceive([
        'getId'       => $id,
        'getLastDate' => $lastDate ? $lastDate->copy() : null
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


  // **************************
  // Tests for existing courses
  // **************************

  public function testCourseExists() {
    $startDate = Date::today()->addWeek();
    $endDate = $startDate->copy()->addMonth();
    $teacher = $this->mockModel(Teacher::class);

    $this->createService();
    $this->mockLessons(['forTeacher' => $teacher], [['date' => $startDate->copy()->addWeeks(3), 'withCourse' => true]]);

    $this->assertException(function() use ($teacher, $startDate, $endDate) {
      $this->courseService->coursePossible($teacher, $startDate, $endDate, [1]);
    }, CourseException::class, CourseException::EXISTS);
  }

  public function testObligatoryExists() {
    $startDate = Date::today()->addWeek();
    $endDate = $startDate->copy()->addMonth();

    $this->createService();

    /** @var Builder $groups */
    $groups = Mockery::mock(Builder::class);

    $this->mockLessons(['forGroups' => $groups], [['date' => $startDate->copy()->addWeeks(3)]]);

    $this->assertException(function() use ($startDate, $endDate, $groups) {
      $this->courseService->obligatoryPossible($groups, $startDate, $endDate, [1]);
    }, CourseException::class, CourseException::OBLIGATORY_EXISTS);
  }

  // ******************************
  // Failing tests for createCourse
  // ******************************

  public function testSaveFailed() {
    $startDate = Date::today()->addWeek();

    /** @var Course $course */
    $course = $this->mockModel(Course::class);
    $teacher = $this->mockModel(Teacher::class);

    $this->mockCoursePossible($teacher, $startDate);
    $this->mockLessons();
    $course->shouldReceive(['save' => false])->between(1, 1);

    $courseSpec = $this->mockCreateSpec($startDate, null, $course);

    $this->assertException(function() use ($courseSpec, $teacher) {
      $this->courseService->createCourse($courseSpec, $teacher);
    }, CourseException::class, CourseException::SAVE_FAILED);
  }

  // *********************************
  // Successful tests for createCourse
  // *********************************

  private function create(Date $startDate, Date $endDate = null, array $numbers, array $lessons, array $offdays, array $expected, array $groups = null) {
    /** @var Course $course */
    $course = $this->mockModel(Course::class);
    $teacher = $this->mockModel(Teacher::class, ['id' => 0]);

    $this->mockCoursePossible($teacher, $startDate, $endDate, $numbers);
    $this->mockOffdaysInRange($offdays);

    $this->mockLessons(['forTeacher' => $teacher], $lessons);

    $relation = Mockery::mock(Relation::class);
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

    $courseSpec = $this->mockCreateSpec($startDate, $endDate, $course, $numbers, $groups);

    $expectedCount = array_sum(array_map('count', $expected));

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $relation->shouldReceive('save')
        ->between($expectedCount, $expectedCount)
        ->with(Mockery::on(function(Lesson $lesson) use ($expected) {
          if (!isset($expected[$lesson->number]) || !in_array($lesson->date, $expected[$lesson->number])) {
            $this->fail('Did not expect lesson ' . $lesson->number . ' at ' . $lesson->date);
          }
          return true;
        }));

    $this->courseService->createCourse($courseSpec, $teacher);
  }

  public function testCreateSingleDay() {
    $startDate = Date::today()->addWeek();

    $d1 = $startDate->copy()->addWeek();
    $d2 = $startDate->copy()->addWeeks(2);
    $d4 = $startDate->copy()->addWeeks(4);

    $lessons = [$startDate, $d1, $d4];
    $offdays = [$d2];
    $expected = [1 => [$startDate], 2 => [$startDate]];

    $this->create($startDate, null, [1, 2], $lessons, $offdays, $expected);
  }

  public function testCreateRepeated() {
    $startDate = Date::today()->addWeek();
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
    $startDate = Date::today()->addWeek();
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
    $startDate = Date::today()->addWeek();
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
