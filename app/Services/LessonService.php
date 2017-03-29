<?php

namespace App\Services;

use App\Exceptions\LessonException;
use App\Models\Teacher;
use Carbon\Carbon;

interface LessonService {

  /**
   * Validate and return the requested lessons for the given day of week
   *
   * @param int $dayOfWeek
   * @param int $firstLesson
   * @param int|null $lastLesson
   * @return int[] All lesson numbers
   * @throws LessonException No lessons on given day of week or range of lessons invalid
   */
  public function getLessonsForDay($dayOfWeek, $firstLesson = 1, $lastLesson = null);

  /**
   * @param Teacher $teacher
   * @param Carbon $start Start date
   * @param Carbon|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return \Illuminate\Database\Query\Builder
   */
  public function forTeacher(Teacher $teacher, Carbon $start, Carbon $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false, $withCourse = false);

  /**
   * @param int[] $groups
   * @param Carbon $start Start date
   * @param Carbon|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @return \Illuminate\Database\Query\Builder
   */
  public function forGroups(array $groups, Carbon $start, Carbon $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false);

}