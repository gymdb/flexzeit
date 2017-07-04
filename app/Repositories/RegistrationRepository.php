<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface RegistrationRepository {

  /**
   * @param Student|Group $student
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|int[]|null $number Only show the given lesson numbers
   * @param bool $showCancelled
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function forStudent($student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null);

  /**
   * @param Group $group
   * @param Student|null $student
   * @param Date $start
   * @param Date $end
   * @return Builder
   */
  public function getMissing(Group $group, Student $student = null, Date $start, Date $end);

  /**
   * @param Student $student
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function getSlots(Student $student, Date $start, Date $end = null);

}
