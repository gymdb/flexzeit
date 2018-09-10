<?php

namespace App\Helpers;

use DatePeriod;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Helper class for making usage of PHPs DatePeriod
 *
 * @package App\Helpers
 */
class DateRange extends DatePeriod implements Arrayable {

  /**
   * Construct a range including all the days on a given day of week within the range
   *
   * @param Date $start
   * @param Date $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   * @param int|null $frequency Frequency in weeks (defaults to weekly, ignored if $dayOfWeek is null)
   */
  public function __construct(Date $start, Date $end, $dayOfWeek = null, int $frequency = null) {
    if (is_null($dayOfWeek)) {
      $interval = CarbonInterval::day();
    } else {
      $start = $start->copy()->setToDayOfWeek($dayOfWeek);
      $interval = CarbonInterval::week($frequency ?? 1);
    }

    parent::__construct($start, $interval, $end->copy()->addDay());
  }

  /**
   * Return an array containing all the days on a given day of week within the range
   *
   * @param Date $start
   * @param Date $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   * @param int|null $frequency Frequency in weeks (defaults to weekly, ignored if $dayOfWeek is null)
   * @return Date[]
   */
  public static function getDates(Date $start, Date $end, $dayOfWeek = null, int $frequency = null) {
    return (new DateRange($start, $end, $dayOfWeek, $frequency))->toArray();
  }

  /**
   * Return a collection containing all the days on a given day of week within the range
   *
   * @param Date $start
   * @param Date $end
   * @param int|null $dayOfWeek Day of week, as defined by the constants in Carbon. Null if every day should be included
   * @param int|null $frequency Frequency in weeks (defaults to weekly, ignored if $dayOfWeek is null)
   * @return Collection
   */
  public static function getCollection(Date $start, Date $end, $dayOfWeek = null, int $frequency = 1) {
    return (new DateRange($start, $end, $dayOfWeek, $frequency))->toCollection();
  }

  /**
   * Return an array containing all dates in this range
   *
   * @return Date[]
   */
  public function toArray() {
    return iterator_to_array($this);
  }

  /**
   * Return a collection containing all days in range
   *
   * @return Collection
   */
  public function toCollection() {
    return collect($this);
  }

}