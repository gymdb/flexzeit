<?php

namespace App\Repositories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface RoomRepository {

  /**
   * @return Builder
   */
  public function query();

  /**
   * @return Builder
   */
  public function queryTypes();

  /**
   * @param Collection $lessons
   * @param Teacher $teacher Teacher to ignore for occupation
   * @return Builder
   */
  public function queryOccupation(Collection $lessons, Teacher $teacher);

}
