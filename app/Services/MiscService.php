<?php

namespace App\Services;

use App\Models\Group;
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
   * Get a list of all students in the given group, sorted by name
   *
   * @param Group $group
   * @return Collection
   */
  public function getStudents(Group $group);

  /**
   * Get a list of all subjects, sorted by name
   *
   * @return Collection
   */
  public function getSubjects();

}