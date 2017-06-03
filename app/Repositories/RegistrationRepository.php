<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface RegistrationRepository {

  /**
   * Build a query for all lessons within a given range
   *
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param null $dayOfWeek Only show dates on the given day of week
   * @param Relation|null $relation Relation to run the query on
   * @return Builder
   */
  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, Relation $relation = null);

  /**
   * @param Teacher $teacher
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @return Builder
   */
  public function forTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null);

  /**
   * @param Student $student
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function forStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, Teacher $teacher = null, Subject $subject = null);

}
