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

}
