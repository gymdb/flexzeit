<?php

namespace App\Repositories;

use App\Models\Offday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface OffdayRepository {

  /**
   * @param int $id
   * @return Offday|null
   */
  public function find($id);

  /**
   * @return Collection|Offday[]
   */
  public function all();

  /**
   * Build a query for all lessons within a given range
   *
   * @param Carbon $start Start date
   * @param Carbon|null $end Optional end date (start day only if empty)
   * @param null $dayOfWeek Only show dates on the given day of week
   * @param Builder|null $query Preexisting query builder to extend
   * @return Builder
   */
  public function inRange(Carbon $start, Carbon $end = null, $dayOfWeek = null, Builder $query = null);

}
