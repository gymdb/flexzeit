<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

/**
 * Repository for accessing the lessons table
 *
 * @package App\Repository
 */
interface LessonRepository {

  /**
   * @param Teacher $teacher
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $number Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Builder
   */
  public function queryForTeacher(Teacher $teacher = null, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false);

  /**
   * @param Student $student
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $number Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Builder
   */
  public function queryForStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false);

  /**
   * @param Student $student
   * @param Date $date
   * @param array $numbers
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param string|null $type
   * @return Builder
   */
  public function queryAvailable(Student $student, Date $date, array $numbers, Teacher $teacher = null, Subject $subject = null, $type = null);

  /**
   * @param array $groups
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $number Only show lessons with these numbers
   * @param Course|null $exclude Exclude lessons from this course
   * @return Builder
   */
  public function queryForGroups(array $groups, Date $start, Date $end = null, $dayOfWeek = null, $number = null, Course $exclude = null);

}
