<?php

namespace App\Services;

use App\Helpers\Date;
use Illuminate\Support\Collection;

interface WebUntisService {

  /**
   * Get a list of all absences
   *
   * @param Date $date Date to load
   * @return Collection<array>
   */
  public function getAbsences(Date $date);

  /**
   * Get school-wide days without lessons
   *
   * @return Collection<Date> List of dates
   */
  public function getOffdays();

}