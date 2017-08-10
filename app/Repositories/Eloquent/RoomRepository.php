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

  public function queryOccupation(Collection $lessons, Teacher $teacher) {
    return Room::with(['lessons.teacher', 'lessons' => function($with) use ($lessons, $teacher) {
      $with->where('cancelled', false)->where('teacher_id', '!=', $teacher->id);
      $with->where(function($query) use ($lessons) {
        foreach ($lessons as $lesson) {
          $query->orWhere([
              'date'   => $lesson['date'],
              'number' => $lesson['number']
          ]);
        }
      });
    }]);
  }

}
