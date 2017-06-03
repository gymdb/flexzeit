<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

/**
 * Repository for accessing the teachers table
 *
 * @package App\Repository
 */
interface TeacherRepository {

  /**
   * @return Builder
   */
  public function query();

}
