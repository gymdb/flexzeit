<?php

namespace App\Repositories;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface OffdayRepository {

  /**
   * Build a query for all lessons within a given range
   *
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int|int[]|null $number
   * @param Relation|null $relation Relation to run the query on
   * @return Builder
   */
  public function queryInRange(Date $start, Date $end = null, $dayOfWeek = null, $number = null, Relation $relation = null);

  /**
   * Build a query for all lessons without an assigned group
   *
   * @return Builder
   */
  public function queryWithoutGroup();

  /**
   * Build a query for all lessons within a given range assigned to a group
   *
   * @param Date $start Start date
   * @param Date $end Optional end date
   * @return Builder
   */
  public function queryWithGroup(Date $start, Date $end);

  /**
   * @param Collection $ids
   */
  public function deleteById(Collection $ids);

  /**
   * @param Collection $offdays
   */
  public function insert(Collection $offdays);

}
