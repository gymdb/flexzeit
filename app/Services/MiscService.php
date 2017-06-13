<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

interface MiscService {

  /**
   * Get a list of all teachers, sorted by name
   *
   * @return Collection
   */
  public function getTeachers();

  /**
   * Get a list of all groups, sorted by name
   *
   * @return Collection
   */
  public function getGroups();

  /**
   * Get a list of all subjects, sorted by name
   *
   * @return Collection
   */
  public function getSubjects();

}