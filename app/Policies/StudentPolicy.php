<?php

namespace App\Policies;

use App\Models\Student;
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
  public function showRegistrations(User $user, Student $student = null) {
    if (!$user->isTeacher()) {
      return false;
    }
    if ($user->admin) {
      return true;
    }
    return $user->form && (!$student || $student->groups()->wherePivot('group_id', $user->form->group_id)->exists());
  }

  /**
   * Determine whether the user can view registrations
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showMissingDocumentation(User $user, Student $student) {
    if (!$user->isTeacher()) {
      return false;
    }
    if ($user->admin) {
      return true;
    }
    return $user->form && (!$student || $student->groups()->wherePivot('group_id', $user->form->group_id)->exists());
  }

  /**
   * Determine whether the user can view feedback
   *
   * @param User $user
   * @param Student|null $student
   * @return bool
   */
  public function showFeedback(User $user, Student $student = null) {
    if (!$user->isTeacher()) {
      return false;
    }
    return $user->form && (!$student || $student->groups()->wherePivot('group_id', $user->form->group_id)->exists());
  }

}
