<?php

namespace App\Repositories\Eloquent;

use App\Models\Room;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class RoomRepository implements \App\Repositories\RoomRepository {

  public function query() {
    return Room::query();
  }

  public function queryTypes() {
    return Room::whereNotNull('type')->distinct()->orderBy('type');
  }

}
