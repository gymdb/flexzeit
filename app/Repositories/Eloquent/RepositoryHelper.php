<?php
namespace App\Repositories\Eloquent;

use App\Helpers\DateRange;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class RepositoryHelper {

  /**
   * Restrict a query to a date, range of dates or day of week within a range of dates
   *
   * @param Builder $query
   * @param Carbon $from
   * @param Carbon|null $to
   * @param int|null $dayOfWeek
   * @return Builder
   */
  public static function inRange(Builder $query, Carbon $from, Carbon $to = null, $dayOfWeek = null) {
    if (is_null($to)) {
      return $query->where('date', $from);
    }

    if (is_null($dayOfWeek)) {
      return $query
          ->whereBetween('date', [$from, $to])
          ->orderBy('date', 'asc');
    }

    return $query
        ->whereIn('date', DateRange::getDates($from, $to, $dayOfWeek))
        ->orderBy('date', 'asc');
  }

}