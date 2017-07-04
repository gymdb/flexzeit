<?php

namespace App\Services;

use App\Helpers\Date;

interface OffdayService {

  /**
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @return string[]
   */
  public function getInRange(Date $start, Date $end = null, $dayOfWeek = null);

  /**
   * Replace school-wide days without lessons with the ones loaded from Untis
   */
  public function loadOffdays();

}
