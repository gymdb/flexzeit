<?php

namespace Tests\Unit;

use App\Helpers\Date;
use App\Helpers\DateRange;
use Tests\TestCase;

/**
 * Test the DateRange class
 *
 * @package Tests\Unit
 */
class DateRangeTest extends TestCase {

  public function testEmptyRange() {
    $range = new DateRange(Date::createFromDate(2017, 2, 28), Date::createFromDate(2017, 2, 27));
    $this->checkCount($range, 0);
  }

  public function testEmptyDayOfWeek() {
    $start = Date::createFromDate(2017, 2, 27);
    $end = Date::createFromDate(2017, 3, 4);
    $dayOfWeek = Date::SUNDAY;

    for ($i = 0; $i < 7; $i++) {
      $this->checkCount(new DateRange($start, $end, $dayOfWeek), 0);
      $dayOfWeek = ($dayOfWeek + 1) % 7;
      $start->addDay();
      $end->addDay();
    }
  }

  public function testSingleDate() {
    $range = new DateRange(Date::createFromDate(2017, 2, 28), Date::createFromDate(2017, 2, 28));
    $this->checkCount($range, 1);
  }

  public function testMultipleDates() {
    $range = new DateRange(Date::createFromDate(2017, 2, 28), Date::createFromDate(2017, 3, 8));
    $this->checkCount($range, 9);
  }

  public function testMultipleDayOfWeek() {
    $start = Date::createFromDate(2017, 2, 27);
    $end = Date::createFromDate(2017, 3, 19);
    $dayOfWeek = Date::SUNDAY;

    for ($i = 0; $i < 7; $i++) {
      $this->checkCount(new DateRange($start, $end, $dayOfWeek), 3);
      $dayOfWeek = ($dayOfWeek + 1) % 7;
      $start->addDay();
      $end->addDay();
    }
  }

  public function testToArray() {
    $range = new DateRange(Date::createFromDate(2017, 2, 28), Date::createFromDate(2017, 3, 8));
    $this->checkArray($range->toArray(), 9);
  }

  private function checkCount(DateRange $range, $count) {
    $i = 0;
    foreach ($range as $date) {
      $this->assertNotEmpty($date);
      $i++;
    }
    $this->assertEquals($count, $i);
  }

  protected function checkArray(array $arr, $count) {
    $this->assertEquals($count, count($arr));
    foreach ($arr as $date) {
      $this->assertNotEmpty($date);
    }
  }

}
