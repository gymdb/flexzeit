<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Offday;
use Illuminate\Database\Eloquent\Relations\Relation;

class OffdayRepository implements \App\Repositories\OffdayRepository {

  use RepositoryTrait;

  public function queryInRange(Date $start, Date $end = null, $dayOfWeek = null, $number = null, Relation $relation = null) {
    $query = $this->inRange($relation ? $relation->getQuery() : Offday::doesntHave('group'), $start, $end, $dayOfWeek, $number);
    if (is_null($number)) {
      $query->whereNull('number');
    }
    return $query;
  }

  public function deleteWithoutGroup() {
    Offday::whereNull('group_id')->delete();
  }

}
