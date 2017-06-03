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
    // TODO
    return $user->isTeacher() && $registration->lesson->teacher->id === $user->id;
  }

  /**
   * Determine whether the user can write feedback for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function writeFeedback(User $user, Registration $registration) {
    return $user->isTeacher() && $registration->lesson->teacher->id === $user->id;
  }

  /**
   * Determine whether the user can set attendance for this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function setAttendance(User $user, Registration $registration) {
    return $user->isTeacher() && $registration->lesson->teacher->id === $user->id;
  }

  /**
   * Determine whether the user can unregister this registration.
   *
   * @param  User $user
   * @param  Registration $registration
   * @return bool
   */
  public function unregister(User $user, Registration $registration) {
    return $user->isTeacher() && ($user->admin || $registration->lesson->teacher->id === $user->id);
  }

}
