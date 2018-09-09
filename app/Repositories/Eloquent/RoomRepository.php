<?php

namespace App\Repositories\Eloquent;

use App\Helpers\DateConstraints;
use App\Models\Room;
use App\Models\RoomOccupation;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class RoomRepository implements \App\Repositories\RoomRepository {

  use RepositoryTrait;

  public function query() {
    return Room::query();
  }

  public function queryTypes() {
    return Room::whereNotNull('type')->distinct()->orderBy('type');
  }

  public function queryOccupations(DateConstraints $constraints) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return $this->inRange(RoomOccupation::query(), $constraints);
  }

  public function queryOccupationForLessons(Collection $lessons, Teacher $teacher) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = RoomOccupation::where(function($or) use ($teacher) {
      $or->whereNull('teacher_id')->orWhere('teacher_id', '!=', $teacher->id);
    });
    return $this->restrictToLessons($query, $lessons);
  }

  public function deleteOccupationsById(Collection $ids) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    RoomOccupation::whereIn('id', $ids)->delete();
  }

  public function insertOccupations(Collection $occupations) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    RoomOccupation::insert($occupations->toArray());
  }

}
