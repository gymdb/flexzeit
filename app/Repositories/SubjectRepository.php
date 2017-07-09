<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface SubjectRepository {

  /**
   * @return Builder
   */
  public function query();

}
