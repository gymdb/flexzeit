<?php

namespace App\Services;

use App\Helpers\Date;

interface StudentService {

  /**
   * Load absences for a given day
   *
   * @param Date $date
   */
  public function loadAbsences(Date $date);

}