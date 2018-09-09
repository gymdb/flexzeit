<?php

namespace App\Services;

use App\Helpers\DateConstraints;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Collection;

interface DocumentationService {

  /**
   * Add documentation for a lesson
   *
   * @param Registration $registration Registration loaded from the database
   * @param string $documentation
   */
  public function setDocumentation(Registration $registration, $documentation);

  /**
   * Give feedback for a particular registration
   *
   * @param Registration $registration
   * @param string $feedback
   */
  public function setFeedback(Registration $registration, $feedback);

  /**
   * @param Student $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Collection<Registration>
   */
  public function getDocumentation(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null);

  /**
   * Get documentation for a student for returning as JSON
   *
   * @param Student $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Collection<array>
   */
  public function getMappedDocumentation(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null);

  /**
   * Get missing documentation for returning as JSON
   *
   * @param Group $group
   * @param Student|null $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @return Collection <array>
   */
  public function getMappedMissing(Group $group, Student $student = null, DateConstraints $constraints, Teacher $teacher = null);

  /**
   * Get feedback for a student for returning as JSON
   *
   * @param Student $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Collection<array>
   */
  public function getMappedFeedback(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null);

}