<?php

namespace App\Helpers;

use DatePeriod;
use Carbon\CarbonInterval;

/**
 * Helper class for making usage of PHPs DatePeriod
 *
 * @package App\Helpers
 */
class DateRange extends DatePeriod {

  /**
   * Construct a range including all the days on a given day of week within the range
   *
   * @param Date $start
   * @param Date $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   */
  public function __construct(Date $start, Date $end, $dayOfWeek = null) {
    if (is_null($dayOfWeek)) {
      $interval = CarbonInterval::day();
    } else {
      $start = $start->copy()->setToDayOfWeek($dayOfWeek);
      $interval = CarbonInterval::week();
    }

    parent::__construct($start, $interval, $end->copy()->addDay());
  }

  /**
   * Return an array containing all the days on a given day of week within the range
   *
   * @param Date $start
   * @param Date $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   * @return Date[]
   */
  public static function getDates(Date $start, Date $end, $dayOfWeek = null) {
    return (new DateRange($start, $end, $dayOfWeek))->toArray();
  }

  /**
   * Return an array containing all dates in this range
   *
   * @return Date[]
   */
  public function toArray() {
    return iterator_to_array($this);
  }

}