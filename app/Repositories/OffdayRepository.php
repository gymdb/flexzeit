<?php

namespace App\Repositories;

use App\Helpers\DateConstraints;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface OffdayRepository {

  /**
   * Build a query for all lessons within a given range
   *
   * @param DateConstraints $constraints
   * @param Relation|null $relation Relation to run the query on
   * @return Builder
   */
  public function queryInRange(DateConstraints $constraints, Relation $relation = null);

  /**
   * Build a query for all offdays for the student's groups during the given lessons
   *
   * @param Collection $lessons Collection of Lesson objects
   * @param Student $student
   * @return Builder
   */
  public function queryForLessonsWithStudent(Collection $lessons, Student $student);

  /**
   * Build a query for all offdays for all students in the given groups during the given lessons
   *
   * @param Collection $lessons Collection of Lesson objects
   * @param int[] $groups
   * @return Builder
   */
  public function queryForLessonsWithGroups(Collection $lessons, array $groups);

  /**
   * Build a query for all lessons without an assigned group
   *
   * @return Builder
   */
  public function queryWithoutGroup();

  /**
   * Build a query for all lessons within a given range assigned to a group
   *
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function queryWithGroup(DateConstraints $constraints);

  /**
   * @param Collection $ids
   */
  public function deleteById(Collection $ids);

  /**
   * @param Collection $offdays
   */
  public function insert(Collection $offdays);

}
