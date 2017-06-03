<?php

namespace App\Repositories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

/**
 * Repository for accessing the groups table
 *
 * @package App\Repository
 */
interface GroupRepository {

  /**
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function query();

  /**
   * @param int|int[] $ids
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function queryById($ids);

}
