<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;

class GroupRepository implements \App\Repositories\GroupRepository {

  public function find($id) {
    return Group::find($id);
  }

  public function all() {
    return Group::all();
  }

  public function queryById($ids) {
    return Group::whereKey($ids);
  }

}