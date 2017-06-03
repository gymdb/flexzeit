<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Repository for accessing the lessons table
 *
 * @package App\Repository
 */
interface LessonRepository {

  /**
   * Build a query for all lessons within a given range
   *
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @param Relation|null $relation Relation to run the query on
   * @return Builder
   */
  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false, $withCourse = false, Relation $relation = null);

  /**
   * @param Teacher $teacher
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Builder
   */
  public function forTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false, $withCourse = false);

  /**
   * @param Student $student
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Builder
   */
  public function forStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false, $withCourse = false);

  /**
   * @param Builder $groups
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @return Builder
   */
  public function forGroups(Builder $groups, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false);

}
