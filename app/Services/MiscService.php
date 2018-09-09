<?php

namespace App\Services;

use App\Models\Teacher;
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
   * @param Teacher|null $teacher
   * @return Collection
   */
  public function getGroups(Teacher $teacher = null);

  /**
   * Get a list of all subjects, sorted by name
   *
   * @return Collection
   */
  public function getSubjects();

  /**
   * Get a list of all rooms, sorted by name
   *
   * @return Collection
   */
  public function getRooms();

  /**
   * Get a list of all room types, sorted alphabetically
   *
   * @return Collection
   */
  public function getRoomTypes();

}