<?php

namespace App\Repositories;

use App\Helpers\DateConstraints;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the lessons table
 *
 * @package App\Repository
 */
interface LessonRepository {

  /**
   * @param Teacher $teacher
   * @param DateConstraints $constraints
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Builder
   */
  public function queryForTeacher(Teacher $teacher = null, DateConstraints $constraints, $showCancelled = false, $withCourse = false);

  /**
   * @param Student $student
   * @param DateConstraints $constraints
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Builder
   */
  public function queryForStudent(Student $student, DateConstraints $constraints, $showCancelled = false, $withCourse = false);

  /**
   * Query lessons corresponding to the given substitution data
   *
   * @param Collection $substitutions
   * @return Builder
   */
  public function queryForSubstitutions(Collection $substitutions);

  /**
   * @param Collection $lessons
   * @param Teacher $teacher Teacher to ignore for occupation
   * @return Builder
   */
  public function queryForOccupation(Collection $lessons, Teacher $teacher);

  /**
   * @param Student $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param string|null $type
   * @return Builder
   */
  public function queryAvailable(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null, $type = null);

  /**
   * @param Builder $query
   * @return Builder
   */
  public function addParticipants(Builder $query);

  /**
   * @param array $groups
   * @param Collection $lessons Collection of lesson objects during which to query
   * @param Course|null $exclude Exclude lessons from this course
   * @return Builder
   */
  public function queryForGroups(array $groups, Collection $lessons, Course $exclude = null);

  /**
   * @param Collection $lessons
   * @param Course $course
   */
  public function assignCourse(Collection $lessons, Course $course);

  /**
   * @param Collection $lessons
   * @param Course $course
   */
  public function createWithCourse(Collection $lessons, Course $course);

}
