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
   * @param int $id
   * @return Group|null
   */
  public function find($id);

  /**
   * @return Group[]|Collection
   */
  public function all();



  /**
   * @param int|int[] $ids
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function queryById($ids);

}
