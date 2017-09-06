<?php

namespace App\Policies;

use App\Models\Registration;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegistrationPolicy {

  use HandlesAuthorization;

  /**
   * Determine whether the user can read feedback for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function readFeedback(User $user, Registration $registration) {
    return $user->isTeacher() && $registration->lesson->teacher_id === $user->id;
  }

  /**
   * Determine whether the user can write feedback for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function writeFeedback(User $user, Registration $registration) {
    return $user->isTeacher() && $registration->lesson->teacher_id === $user->id;
  }

  /**
   * Determine whether the user can read documentation for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function readDocumentation(User $user, Registration $registration) {
    return $user->isStudent() && $registration->student_id === $user->id;
  }

  /**
   * Determine whether the user can write documentation for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function writeDocumentation(User $user, Registration $registration) {
    return $user->isStudent() && $registration->student_id === $user->id;
  }

  /**
   * Determine whether the user can set attendance for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function setAttendance(User $user, Registration $registration) {
    if (!$user->isTeacher()) {
      return false;
    }
    if ($user->admin) {
      return true;
    }

    return $registration->lesson->teacher_id === $user->id
        || ($user->form && $user->form->students()->wherePivot('student_id', $registration->student_id)->exists());
  }

  /**
   * Determine whether the user can unregister this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function unregister(User $user, Registration $registration) {
    if ($user->isStudent()) {
      return $registration->student_id === $user->id;
    }
    return $user->isTeacher() && ($user->admin || $registration->lesson->teacher_id === $user->id);
  }

}
