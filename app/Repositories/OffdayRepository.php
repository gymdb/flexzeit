<?php

namespace App\Repositories;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

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
   * @param null $dayOfWeek Only show dates on the given day of week
   * @param Relation|null $relation Relation to run the query on
   * @return Builder
   */
  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, Relation $relation = null);

}
