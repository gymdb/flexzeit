<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Helpers\DateRange;
use Illuminate\Database\Eloquent\Builder;

trait RepositoryTrait {

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
  protected function inRange($query, Date $from, Date $to = null, $dayOfWeek = null, $number = null, $table = '') {
    if (!is_null($number) && empty($this->noNumber)) {
      $query->where(function($query) use ($number, $table) {
        $query->whereIn($table . 'number', is_scalar($number) ? [$number] : $number)
            ->orWhereNull($table . 'number');
      });
    }

    $query->orderBy($table . 'date');
    if (empty($this->noNumber)) {
      $query->orderBy($table . 'number');
    }

    if (is_null($to)) {
      return $query->where($table . 'date', $from);
    }

    return is_null($dayOfWeek)
        ? $query->whereBetween($table . 'date', [$from, $to])
        : $query->whereIn($table . 'date', DateRange::getDates($from, $to, $dayOfWeek));
  }

}
