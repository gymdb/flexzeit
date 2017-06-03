<?php

namespace Tests\Unit\RegisterService;

use App\Exceptions\RegistrationException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Services\RegistrationService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Tests\Traits\MockConfigTrait;
use Tests\Traits\MockDbTrait;
use Tests\Traits\MockLessonsTrait;
use Tests\Traits\MockOffdaysTrait;
use VladaHejda\AssertException;

/**
 * Test the CreateCourseSpecification classes
 *
 * @package Tests\Unit
 */
class StudentLessonTest extends RegistrationServiceTest {

  use MockConfigTrait;
  use MockDbTrait;
  use MockOffdaysTrait;
  use MockLessonsTrait;
  use AssertException;

  /** @var RegistrationService */
  private $registrationService;

  protected function setUp() {
    parent::setUp();
    $this->mockConfig([]);
    $this->mock(['lessons' => LessonRepository::class, 'offdays' => OffdayRepository::class]);
    $this->registrationService = $this->app->make(RegistrationService::class);
  }

  /**
   * @param Date $date
   * @param bool $hasCourse
   * @return Lesson
   */
  private function mockLesson(Date $date, $hasCourse = false) {
    $lesson = $this->mockModel(Lesson::class, [
        'date'   => $date,
        'course' => $hasCourse ? $this->mockModel(Course::class) : null
    ]);

    $course = \Mockery::mock(Relation::class);
    $course->shouldReceive(['exists' => $hasCourse]);

    $students = \Mockery::mock(Relation::class);
    $students->shouldReceive(['count' => 0]);

    $lesson->shouldReceive(compact('course', 'students'));

    return $lesson;
  }

  private function mockStudent(array $registrations = [], array $offdays = []) {
    $student = $this->mockModel(Student::class);

    $relation = \Mockery::mock(Relation::class);
    $student->shouldReceive(['offdays' => $relation]);

    $this->mockLessons(['forStudent' => $student], $registrations);
    $this->mockOffdaysInRange($offdays, $relation);

    return $student;
  }

  // *****************************
  // Actual execution
  // *****************************

  private function runStudentLesson($lesson, $student, $force, $admin, $code = null) {
    if ($code) {
      $this->assertException(function() use ($lesson, $student, $force, $admin) {
        $this->registrationService->registerStudentForLesson($lesson, $student, $force, $admin);
      }, RegistrationException::class, $code);
    } else {
      //$this->registrationService->registerStudentForLesson($lesson, $student, $force, $admin);
    }
  }

  private function runOffday($success = false, $force = false, $admin = false) {
    $date = Date::today()->addWeek();

    $this->mockConfig([
        'registration.begin.week' => 0, 'registration.begin.day' => 8,
        'registration.end.week'   => 0, 'registration.end.day' => 7,
        'lesson.maxstudents'      => 10
    ]);

    $student = $this->mockStudent([], [$date->copy()]);
    $lesson = $this->mockLesson($date);
    $this->runStudentLesson($lesson, $student, $force, $admin, $success ? null : RegistrationException::OFFDAY);
  }

  private function runMaxStudents($success = false, $force = false, $admin = false) {
  }

  private function runHasCourse($success = false, $force = false, $admin = false) {
  }

  private function runAlreadyRegistered($success = false, $force = false, $admin = false) {
  }

  private function runRegistrationPeriod($success = false, $force = false, $admin = false) {
  }

  private function runLessonAlreadyHeld($success = false, $admin = false) {
  }

  private function runSuccess($force = false, $admin = false) {
  }


  // *****************************
  // Registration with force=false
  // *****************************

  public function testOffday() {
    $this->runOffday();
  }

  public function testMaxStudents() {
    $this->runMaxStudents();
  }

  public function testHasCourse() {
    $this->runHasCourse();
  }

  public function testAlreadyRegistered() {
    $this->runAlreadyRegistered();
  }

  public function testRegistrationPeriod() {
    $this->runRegistrationPeriod();
  }

  public function testNoForce() {
    $this->runSuccess();
  }

  // *****************************
  // Registration with admin=false
  // *****************************

  public function testAlreadyRegisteredForce() {
    $this->runAlreadyRegistered(false, true);
  }

  public function testLessonAlreadyHeld() {
    $this->runLessonAlreadyHeld();
  }

  public function testForce() {
    $this->runOffday(true, true);
    $this->runMaxStudents(true, true);
    $this->runHasCourse(true, true);
    $this->runRegistrationPeriod(true, true);
    $this->runSuccess(true);
  }

  // *****************************
  // Registration with admin=true
  // *****************************

  public function testAdmin() {
    $this->runOffday(true, true, true);
    $this->runMaxStudents(true, true, true);
    $this->runHasCourse(true, true, true);
    $this->runAlreadyRegistered(true, true, true);
    $this->runRegistrationPeriod(true, true, true);
    $this->runLessonAlreadyHeld(true, true, true);
    $this->runSuccess(true, true);
  }

}
