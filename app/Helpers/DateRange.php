<?php

namespace App\Helpers;

use Carbon\Carbon;
use Carbon\CarbonInterval;

/**
 * Helper class for making usage of PHPs DatePeriod
 *
 * @package App\Helpers
 */
class DateRange extends \DatePeriod {

  /**
   * Names of days of the week.
   * Copied from Carbon, where sadly this can't be accessed in any way...
   *
   * @var array
   */
  protected static $days = [
      Carbon::SUNDAY    => 'Sunday',
      Carbon::MONDAY    => 'Monday',
      Carbon::TUESDAY   => 'Tuesday',
      Carbon::WEDNESDAY => 'Wednesday',
      Carbon::THURSDAY  => 'Thursday',
      Carbon::FRIDAY    => 'Friday',
      Carbon::SATURDAY  => 'Saturday',
  ];

  /**
   * Return a string representation of a day of week to be used in DateTime::modify
   *
   * @param int $dayOfWeek
   * @return string
   */
  public static function getDay($dayOfWeek) {
    return static::$days[$dayOfWeek >= 0 ? $dayOfWeek % 7 : 7 - ($dayOfWeek % 7)];
  }

  /**
   * Construct a range including all the days on a given day of week within the range
   *
   * @param Carbon $start
   * @param Carbon $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   */
  public function __construct(Carbon $start, Carbon $end, $dayOfWeek = null) {
    $dateStart = $start->copy()->startOfDay();
    $dateEnd = $end->copy()->startOfDay()->addDay();

    if (is_null($dayOfWeek)) {
      $interval = CarbonInterval::day();
    } else {
      $dateStart->modify(self::getDay($dayOfWeek));
      $interval = CarbonInterval::week();
    }

    parent::__construct($dateStart, $interval, $dateEnd);
  }

  /**
   * Return an array containing all the days on a given day of week within the range
   *
   * @param Carbon $start
   * @param Carbon $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   * @return array
   */
  public static function getDates(Carbon $start, Carbon $end, $dayOfWeek = null) {
    return (new DateRange($start, $end, $dayOfWeek))->toArray();
  }

  /**
   * Return an array containing all dates in this range
   *
   * @return \DateTime[]
   */
  public function toArray() {
    return iterator_to_array($this);
  }

}