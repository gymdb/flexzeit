<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

interface TeacherService {

  public function getAll();

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