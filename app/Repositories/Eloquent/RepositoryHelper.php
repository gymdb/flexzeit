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
   * @param int|int[]|null $number
   * @param string $table
   * @return Builder
   */
  public static function inRange($query, Date $from, Date $to = null, $dayOfWeek = null, $number = null, $table = '') {
    if (!is_null($number)) {
      $query->where(function($query) use ($number, $table) {
        $query->whereIn($table . 'number', is_scalar($number) ? [$number] : $number)
            ->orWhereNull($table . 'number');
      });
    }

    if (is_null($to)) {
      return $query->orderBy($table . 'number')->where($table . 'date', $from);
    }

    $query->orderBy($table . 'date');
    $query->orderBy($table . 'number');
    return is_null($dayOfWeek)
        ? $query->whereBetween($table . 'date', [$from, $to])
        : $query->whereIn($table . 'date', DateRange::getDates($from, $to, $dayOfWeek));
  }

  public static function matcher(Date $date, $number = null, $cancelled = null) {
    return function($item) use ($date, $number, $cancelled) {
      return $item->date == $date
          && (is_null($number) || $item->number === $number)
          && (is_null($cancelled) || $item->cancelled === $cancelled);
    };
  }

}