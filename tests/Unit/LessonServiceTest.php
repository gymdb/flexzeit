<?php

namespace Tests\Unit;

use App\Exceptions\LessonException;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Services\CourseService;
use App\Services\LessonService;
use Tests\TestCase;
use Tests\Traits\MockConfigTrait;
use VladaHejda\AssertException;

/**
 * Test the LessonService implementation
 *
 * @package Tests\Unit
 */
class LessonServiceTest extends TestCase {

  use MockConfigTrait;
  use AssertException;

  /**
   * @var CourseService
   */
  private $lessonService;

  // *******************************
  // Methods for setting up the test
  // *******************************

  /**
   * Create the LessonService
   */
  private function createService() {
    $this->mock(['lessons' => LessonRepository::class, 'groups' => GroupRepository::class]);
    $this->mockConfig([]);
    $this->lessonService = $this->app->make(LessonService::class);
  }

  // **************************
  // Tests for getLessonsForDay
  // **************************

  public function testInvalidStartDate() {
    // Mock config with 2 lessons on monday, 1 on tuesday and 3 on wednesday
    $this->mockConfig(['lesson.count.1' => 2, 'lesson.count.2' => 1, 'lesson.count.3' => 3, 'lesson.count.4' => 0]);
    $this->createService();

    // Create courses on days without flextime
    foreach ([0, 4, 5, 6] as $i) {
      $this->assertException(function() use ($i) {
        $this->lessonService->getLessonsForDay($i, 1, 1);
      }, LessonException::class, LessonException::DAY_OF_WEEK);
    }
  }

  public function testInvalidLessons() {
    // Mock config with 2 lessons on monday, 1 on tuesday and 3 on wednesday
    $this->mockConfig(['lesson.count.1' => 2, 'lesson.count.2' => 1, 'lesson.count.3' => 3, 'lesson.count.4' => 0]);
    $this->createService();

    $values = [
        [1, 0, 1], [1, 2, 1],
        [1, 1, 3], [1, 3, 3],
        [2, 1, 4], [2, 2, 2],
        [3, 3, 4], [3, 4, 5]
    ];

    foreach ($values as $value) {
      $this->assertException(function() use ($value) {
        $this->lessonService->getLessonsForDay($value[0], $value[1], $value[2]);
      }, LessonException::class, LessonException::NUMBERS);
    }
  }

  public function testGetLessonsForDay() {
    // Mock config with 2 lessons on monday, 1 on tuesday and 3 on wednesday
    $this->mockConfig(['lesson.count.1' => 2, 'lesson.count.2' => 1, 'lesson.count.3' => 3, 'lesson.count.4' => 0]);
    $this->createService();

    $values = [
        [1, 1, null, [1, 2]], [1, 1, 1, [1]], [1, 2, 2, [2]],
        [2, 1, null, [1]], [2, 1, 1, [1]],
        [3, 1, null, [1, 2, 3]], [3, 1, 2, [1, 2]], [3, 2, 3, [2, 3]],
    ];

    foreach ($values as $value) {
      $this->assertEquals($value[3], $this->lessonService->getLessonsForDay($value[0], $value[1], $value[2]));
    }
  }

}
