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
  * Determine whether the user can view missing registrations for sports category
  *
  * @param User $user
  * @param Group|null $group
  * @return bool
  */
  public function showMissingSportsRegistration(User $user, Group $group = null) {
    return $this->isFormTeacher($user, $group);
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
   * Determine whether the user can view registrations made by a teacher
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showByTeacherRegistrations(User $user, Student $student = null) {
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
    return $this->isGroupTeacher($user, $student);
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
   * Determine whether the user is the form teacher for the given student
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

  /**
   * Determine whether the user is a teacher that teaches one of the given student's groups
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  private function isGroupTeacher(User $user, Student $student) {
    if (!$user->isTeacher()) {
      return false;
    }
    /** @var Teacher $user */
    if ($user->admin) {
      return true;
    }
    return $student->groups()->whereIn('group_id', $user->groups()->select('group_id')->getQuery())->exists();
  }
}
