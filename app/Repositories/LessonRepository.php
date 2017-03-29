<?php

namespace App\Repositories;

use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

/**
 * Repository for accessing the lessons table
 *
 * @package App\Repository
 */
interface LessonRepository {

  /**
   * @param int $id
   * @return Lesson|null
   */
  public function find($id);

  /**
   * @return Collection|Lesson[]
   */
  public function all();

  /**
   * Build a query for all lessons within a given range
   *
   * @param Carbon $start Start date
   * @param Carbon|null $end Optional end date (start day only if empty)
   * @param null $dayOfWeek Only show dates on the given day of week
   * @param int[] $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @param Builder|null $query Preexisting query builder to extend
   * @return Builder
   */
  public function inRange(Carbon $start, Carbon $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false, $withCourse = false, Builder $query = null);

}
