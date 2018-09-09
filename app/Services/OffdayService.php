<?php

namespace App\Services;

use App\Helpers\Date;

interface OffdayService {

  /**
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $frequency The frequency in weeks, or null for every day
   * @return string[]
   */
  public function getInRange(Date $start, Date $end = null, $frequency = null);

  /**
   * Replace school-wide days without lessons with the ones loaded from Untis
   */
  public function loadOffdays();

  /**
   * Replace school-wide days without lessons with the ones loaded from Untis
   */
  public function loadGroupOffdays();

}
