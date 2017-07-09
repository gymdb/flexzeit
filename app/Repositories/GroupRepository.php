<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

/**
 * Repository for accessing the groups table
 *
 * @package App\Repository
 */
interface GroupRepository {

  /**
   * @return Builder
   */
  public function query();

}
