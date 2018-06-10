<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy {

  use HandlesAuthorization;

  /**
   * Determine whether the user can view registrations
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showRegistrations(User $user, /** @noinspection PhpUnusedParameterInspection */
      Student $student = null) {
    return $user->isTeacher();
  }

  /**
   * Determine whether the user can view missing registrations
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showMissingRegistrations(User $user, Student $student = null) {
    return $this->isFormTeacher($user, $student);
  }

  /**
   * Determine whether the user can view absent students
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showAbsent(User $user, Student $student = null) {
    return $this->isFormTeacher($user, $student);
  }

  /**
   * Determine whether the user can view registrations
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showMissingDocumentation(User $user, Student $student) {
    // TODO This should be group teacher instead of form teacher
    return $this->isFormTeacher($user, $student);
  }

  /**
   * Determine whether the user can view feedback
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showFeedback(User $user, Student $student = null) {
    return $this->isFormTeacher($user, $student, false);
  }

  /**
   * Determine whether the user is a teacher that has access for the given student
   *
   * @param User $user
   * @param Student|null $student
   * @param bool $allowAdmin
   * @return bool
   */
  private function isFormTeacher(User $user, Student $student = null, $allowAdmin = true) {
    if (!$user->isTeacher()) {
      return false;
    }
    /** @var Teacher $user */
    if ($allowAdmin && $user->admin) {
      return true;
    }
    return $user->form && (!$student || $user->form->students()
                ->wherePivot('student_id', $student->id)->exists());
  }

}
