<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BugReportPolicy {

  use HandlesAuthorization;

  /**
   * Determine whether the user can view bug reports
   *
   * @param User $user
   * @return bool
   */
  public function show(User $user) {
    return $user->isTeacher() && $user->admin;
  }

  /**
   * Determine whether the user can trash/restore bug reports
   *
   * @param User $user
   * @return bool
   */
  public function trash(User $user) {
    return $user->isTeacher() && $user->admin;
  }

}
