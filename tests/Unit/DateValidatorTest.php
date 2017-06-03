<?php

namespace Tests\Unit;

use App\Helpers\Date;
use App\Repositories\OffdayRepository;
use App\Validators\DateValidator;
use Tests\TestCase;
use Tests\Traits\MockConfigTrait;
use Tests\Traits\MockDbTrait;
use Tests\Traits\MockOffdaysTrait;

/**
 * Test the DateValidator class
 *
 * @package Tests\Unit
 */
class DateValidatorTest extends TestCase {

  use MockConfigTrait;
  use MockDbTrait;
  use MockOffdaysTrait;

  /** @var DateValidator */
  private $validator;

  protected function setUp() {
    parent::setUp();
    $this->mock(['offdays' => OffdayRepository::class]);
    $this->mockConfig([]);
    $this->validator = $this->app->make(DateValidator::class);
  }

  public function testInYear() {
    $start = Date::createFromDate(2017, 4, 1);
    $end = Date::createFromDate(2017, 5, 31);
    $this->mockConfig(['year.start' => $start, 'year.end' => $end]);

    $last = $end->copy()->addMonth();
    for ($date = $start->copy()->addMonth(-1); $date <= $last; $date->addDay()) {
      $this->assertSame($date->between($start, $end), $this->validator->validateInYear('date', $date));
    }

    $this->assertFalse($this->validator->validateInYear('date', $start->copy()->addDay(-1)));
    $this->assertTrue($this->validator->validateInYear('date', $start));
    $this->assertTrue($this->validator->validateInYear('date', $end));
    $this->assertFalse($this->validator->validateInYear('date', $end->copy()->addDay()));
  }

  public function testCreateAllowed() {
    // Try creating courses if creation is always for dates at least $i days before the course
    for ($i = -2; $i < 10; $i++) {
      $this->mockConfig(['course.create.week' => 0, 'course.create.day' => max(1, $i + 1)]);
      $date = Date::today()->addDay($i);
      $this->assertFalse($this->validator->validateCreateAllowed('date', $date));
      if ($i >= 0) {
        $this->assertTrue($this->validator->validateCreateAllowed('date', $date->addDay()));
      }
    }

    // Try creating courses if creation is always allowed until today's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Date::today()->dayOfWeek]);
      $date = Date::today()->endOfWeek()->addWeek($i - 1);
      $this->assertFalse($this->validator->validateCreateAllowed('date', $date));
      $this->assertTrue($this->validator->validateCreateAllowed('date', $date->addDay()));
    }

    // Try creating courses if creation is always allowed until yesterday's day of week $i weeks before
    for ($i = 1; $i <= 3; $i++) {
      $this->mockConfig(['course.create.week' => $i, 'course.create.day' => Date::today()->addDays(-1)->dayOfWeek]);
      $date = Date::today()->endOfWeek()->addWeek($i);
      $this->assertFalse($this->validator->validateCreateAllowed('date', $date));
      $this->assertTrue($this->validator->validateCreateAllowed('date', $date->addDay()));
    }
  }

  public function testSchoolDay() {
    // Mock a few offdays for the whole school
    $dates = [Date::today()->next(Date::MONDAY), Date::today()->next(Date::WEDNESDAY)];
    $this->mockOffdaysInRange($dates);

    foreach ($dates as $date) {
      $this->assertFalse($this->validator->validateSchoolDay('date', $date));
      $this->assertTrue($this->validator->validateSchoolDay('date', $date->copy()->addDay()));
      $this->assertTrue($this->validator->validateSchoolDay('date', $date->copy()->addWeek()));
    }
  }

  public function testRegistrationAllowed() {
    // Try registration if allowed for courses $i+1 to $i+2 days away
    for ($i = -2; $i < 10; $i++) {
      $this->mockConfig([
          'registration.begin.week' => 0, 'registration.begin.day' => max(3, $i + 3),
          'registration.end.week'   => 0, 'registration.end.day' => max(1, $i + 1)
      ]);

      $date = Date::today()->addDay($i);
      for ($j = 0; $j <= 3; $j++) {
        $k = $j + min(0, $i);
        $this->assertSame($k == 1 || $k == 2, $this->validator->validateRegisterAllowed('date', $date));
        $date->addDay();
      }
    }

    // Registration is allowed for the i-th next week on multiple days of week
    for ($w = 1; $w <= 3; $w++) {
      // Since the week starts with monday start with index 1 for dayOfWeek
      for ($dow = 1; $dow <= 7; $dow++) {
        $bw = $dow < 5 ? $w : $w + 1;
        $this->mockConfig([
            'registration.begin.week' => $bw, 'registration.begin.day' => $dow,
            'registration.end.week'   => $w, 'registration.end.day' => $dow + 3
        ]);

        $today = Date::today()->startOfWeek();
        Date::setTestNow($today);

        for ($i = 1; $i <= 7; $i++) {
          $date = Date::today()->startOfWeek()->addDays(($i > 5 ? $bw : $w) * 7 - 2);
          for ($j = 0; $j <= 10; $j++) {
            $this->assertSame($j >= 2 && $j < 9 && (($i > $dow && $i <= $dow + 3) || $i <= $dow - 4),
                $this->validator->validateRegisterAllowed('date', $date));
            $date->addDay();
          }

          $today->addDay();
        }

        Date::setTestNow();
      }
    }

    // Registration is allowed for the i-th next week on exactly one day of week
    for ($w = 1; $w <= 3; $w++) {
      // Since the week starts with monday start with index 1 for dayOfWeek
      for ($dow = 1; $dow <= 7; $dow++) {
        $this->mockConfig([
            'registration.begin.week' => $dow > 1 ? $w : $w + 1, 'registration.begin.day' => $dow - 1,
            'registration.end.week'   => $w, 'registration.end.day' => $dow
        ]);

        $today = Date::today()->startOfWeek();
        Date::setTestNow($today);
        for ($i = 1; $i <= 7; $i++) {
          $date = Date::today()->startOfWeek()->addDays($w * 7 - 2);
          for ($j = 0; $j <= 10; $j++) {
            $this->assertSame($j >= 2 && $j < 9 && $i === $dow,
                $this->validator->validateRegisterAllowed('date', $date));
            $date->addDay();
          }

          $today->addDay();
        }

        Date::setTestNow();
      }
    }
  }

}
