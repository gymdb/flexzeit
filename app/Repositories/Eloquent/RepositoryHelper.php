<?php
namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Helpers\DateRange;
use Illuminate\Database\Eloquent\Builder;

class RepositoryHelper {

  /**
   * Restrict a query to a date, range of dates or day of week within a range of dates
   *
   * @param Builder $query
   * @param Date $from
   * @param Date|null $to
   * @param int|null $dayOfWeek
   * @return Builder
   */
  public static function inRange(Builder $query, Date $from, Date $to = null, $dayOfWeek = null) {
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