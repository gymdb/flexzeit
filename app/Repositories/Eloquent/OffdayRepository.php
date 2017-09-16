<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Offday;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class OffdayRepository implements \App\Repositories\OffdayRepository {

  use RepositoryTrait;

  public function queryInRange(Date $start, Date $end = null, $dayOfWeek = null, $number = null, Relation $relation = null) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = $this->inRange($relation ? $relation->getQuery() : Offday::whereNull('group_id'), $start, $end, $dayOfWeek, $number);
    if (is_null($number)) {
      $query->whereNull('number');
    }
    return $query;
  }

  public function queryWithoutGroup() {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return Offday::whereNull('group_id');
  }

  public function queryWithGroup(Date $start, Date $end) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return $this->inRange(Offday::whereNotNull('group_id'), $start, $end);
  }

  public function deleteById(Collection $ids) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    Offday::whereIn('id', $ids)->delete();
  }

  public function insert(Collection $offdays) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    Offday::insert($offdays->toArray());
  }

}
