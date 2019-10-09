<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GroupPolicy {

  use HandlesAuthorization;

  /**
   * Determine whether the user can view registrations for this group
   *
   * @param User $user
   * @param Group $group
   * @return bool
   */
  public function showRegistrations(User $user, /** @noinspection PhpUnusedParameterInspection */
      Group $group) {
    return $user->isTeacher();
  }

  /**
   * Determine whether the user can view missing registrations for this group
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  public function showMissingRegistrations(User $user, Group $group = null) {
    return $this->isFormTeacher($user, $group);
  }


  /**
   * Determine whether the user can view missing registrations for sports category
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  public function showMissingSportsRegistration(User $user, Group $group = null) {
    $sportsTeacher = false;
    if (strpos($user->subjects, 'Sport') !== false) {
      $sportsTeacher = true;
    }
    if ($sportsTeacher) {
      return true  ;
    } else {
      return ($user->form && $user->form->group_id < 11) || ($user->admin);
    }
  }

  /**
   * Determine whether the user can absent students for this group
   *
   * @param User $user
   * @param Group $group
   * @return bool
   */
  public function showAbsent(User $user, Group $group) {
    return $this->isFormTeacher($user, $group);
  }

  /**
   * Determine whether the user can view registrations done by teachers for this group
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  public function showByTeacherRegistrations(User $user, Group $group = null) {
    return $this->isFormTeacher($user, $group);
  }

  /**
   * Determine whether the user can view registrations for this group
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  public function showMissingDocumentation(User $user, Group $group) {
    return $this->isGroupTeacher($user, $group);
  }

  /**
   * Allow access only for the form teacher and admin
   *
   * @param User $user
   * @param Group|null $group
   * @return bool
   */
  private function isFormTeacher(User $user, Group $group = null) {
    if (!$user->isTeacher()) {
      return false;
    }
    /** @var Teacher $user */
    if ($user->admin) {
      return true;
    }
    return $group && $user->form && $user->form->group_id === $group->id;
  }

  /**
   * Allow access only for the teachers of the specified group
   *
   * @param User $user
   * @param Group $group
   * @return bool
   */
  private function isGroupTeacher(User $user, Group $group) {
    if (!$user->isTeacher()) {
      return false;
    }
    /** @var Teacher $user */
    if ($user->admin) {
      return true;
    }
    return $user->groups()->whereKey($group->id)->exists();
  }
}
