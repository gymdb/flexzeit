<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

interface CourseRepository {

  /**
   * Return a teacher's courses
   *
   * @param Teacher|null $teacher
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function query(Teacher $teacher = null, Date $start, Date $end = null);

  /**
   * Query all obligatory courses matching the criteria
   *
   * @param Group|null $group
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function queryObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start, Date $end = null);

  /**
   * Query available courses for a student
   *
   * @param Student $student
   * @param Teacher|null $teacher
   * @param Date $start
   * @param Date $end
   * @return Builder
   */
  public function queryAvailable(Student $student, Teacher $teacher = null, Date $start, Date $end = null);

  /**
   * @param Builder|Relation $query
   * @return Builder
   */
  public function addParticipants($query);

}
