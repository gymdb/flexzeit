<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

interface DocumentationService {

  /**
   * Give feedback for a particular registration
   *
   * @param Registration $registration
   * @param string $feedback
   */
  public function setFeedback(Registration $registration, $feedback);

  /**
   * Add documentation for a lesson
   *
   * @param Registration $registration Registration loaded from the database
   * @param string $documentation
   */
  public function setDocumentation(Registration $registration, $documentation);

  /**
   * Get documentation for a student
   *
   * @param Student $student
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param Date|null $start
   * @param Date|null $end
   * @return mixed
   */
  public function getDocumentation(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null);

  /**
   * Get feedback for a student
   *
   * @param Student $student
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param Date|null $start
   * @param Date|null $end
   * @return mixed
   */
  public function getFeedback(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null);

}