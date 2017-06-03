<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;

class GroupRepository implements \App\Repositories\GroupRepository {

  public function query() {
    return Group::query();
  }

  public function queryById($ids) {
    return Group::whereKey($ids);
  }

}