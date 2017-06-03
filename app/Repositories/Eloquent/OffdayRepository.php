<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Offday;
use Illuminate\Database\Eloquent\Relations\Relation;

class OffdayRepository implements \App\Repositories\OffdayRepository {

  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, Relation $relation = null) {
    return RepositoryHelper::inRange($relation ? $relation->getQuery() : Offday::doesntHave('group'), $start, $end, $dayOfWeek);
  }

}