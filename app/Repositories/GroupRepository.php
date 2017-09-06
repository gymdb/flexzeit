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

  /**
   * @param int[] $groups
   * @param int $day
   * @param int $number
   * @return Builder
   */
  public function queryTimetable(array $groups, $day, $number);

}
