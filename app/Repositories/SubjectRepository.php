<?php

namespace App\Repositories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
