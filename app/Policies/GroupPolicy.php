<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy {

  use HandlesAuthorization;

  /**
   * Determine whether the user can view registrations for this group
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  public function showRegistrations(User $user, Group $group) {
    if (!$user->isTeacher()) {
      return false;
    }
    if ($user->admin) {
      return true;
    }
    return $user->form && $user->form->group_id === $group->id;
  }

  /**
   * Determine whether the user can view registrations for this group
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  public function showMissingDocumentation(User $user, Group $group) {
    if (!$user->isTeacher()) {
      return false;
    }
    if ($user->admin) {
      return true;
    }
    return $user->groups()->whereKey($group->id)->exists();
  }

}
