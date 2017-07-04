<?php

namespace App\Policies;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeacherPolicy {

  use HandlesAuthorization;

  public function before(User $user) {
    if ($user->isTeacher() && $user->admin) {
      return true;
    }
    return null;
  }

  /**
   * Determine whether the user can view the list of lessons of this teacher.
   *
   * @param  User $user
   * @param  Teacher $teacher
   * @return bool
   */
  public function viewLessons(User $user, Teacher $teacher) {
    return $user->isTeacher() && $teacher->id === $user->id;
  }

  /**
   * Determine whether the user can view the list of courses of this teacher.
   *
   * @param  User $user
   * @param  Teacher $teacher
   * @return bool
   */
  public function viewCourses(User $user, Teacher $teacher) {
    return $user->isTeacher() && $teacher->id === $user->id;
  }

}
