<?php

namespace App\Repositories;

use App\Helpers\DateConstraints;
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
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function query(Teacher $teacher = null, DateConstraints $constraints);

  /**
   * Query all obligatory courses matching the criteria
   *
   * @param Group|null $group
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function queryObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, DateConstraints $constraints);

  /**
   * Query available courses for a student
   *
   * @param Student $student
   * @param Teacher|null $teacher
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function queryAvailable(Student $student, Teacher $teacher = null, DateConstraints $constraints);

  /**
   * @param Builder|Relation $query
   * @return Builder
   */
  public function addParticipants($query);

}
