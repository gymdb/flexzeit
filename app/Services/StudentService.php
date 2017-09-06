<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Group;
use Illuminate\Support\Collection;

interface StudentService {

  /**
   * Get a list of all students in the given group, sorted by name
   *
   * @param Group $group
   * @return Collection
   */
  public function getStudents(Group $group);

  /**
   * Load absences for a given day
   *
   * @param Date $date
   */
  public function loadAbsences(Date $date);

}