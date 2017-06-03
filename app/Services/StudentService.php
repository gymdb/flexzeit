<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;

interface StudentService {

  /**
   * Get today's lessons for a student
   *
   * @param Student $student
   * @param Date|null $date
   * @return Collection
   */
  public function getRegistrationsForDay(Student $student, Date $date = null);

  /**
   * @param Student $student
   * @return Collection
   */
  public function getUpcomingRegistrations(Student $student);

  /**
   * @param Student $student
   * @return Collection
   */
  public function getDocumentationRegistrations(Student $student);

  /**
   * @param Student $student
   * @param Date $date
   * @return Collection
   */
  public function getAvailableLessons(Student $student, Date $date);

  /**
   * @return Date
   */
  public function getFirstRegisterDate();

  /**
   * @param Date $date
   * @return bool
   */
  public function allowRegistration(Date $date);

}