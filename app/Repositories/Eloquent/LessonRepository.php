<?php

namespace App\Repositories\Eloquent;

use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class LessonRepository implements \App\Repositories\LessonRepository {

  /**
   * @param int $id
   * @return Lesson|null
   */
  public function find($id) {
    return Lesson::find($id);
  }

  /**
   * @return Collection
   */
  public function all() {
    return Lesson::all();
  }

  public function inRange(Carbon $start, Carbon $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false, $withCourse = false, Builder $query = null) {
    $query = RepositoryHelper::inRange($query ?: Lesson::query(), $start, $end, $dayOfWeek);
    if ($withCourse) {
      $query->has('course');
    }
    if (!is_null($numbers)) {
      $query->whereIn('number', $numbers);
    }
    if (!$showCancelled) {
      $query->where('cancelled', false);
    }
    return $query;
  }

}