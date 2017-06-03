<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LessonPolicy {

  use HandlesAuthorization;

  /**
   * Determine whether the user can view the lesson.
   *
   * @param  User $user
   * @param  Lesson $lesson
   * @return bool
   */
  public function view(User $user, Lesson $lesson) {
    return $user->isTeacher() && ($user->admin || $lesson->teacher->id === $user->id);
  }

  /**
   * Determine whether the user can set that attendance has been checked
   *
   * @param  User $user
   * @param  Lesson $lesson
   * @return bool
   */
  public function setAttendanceChecked(User $user, Lesson $lesson) {
    return $user->isTeacher() && $lesson->teacher->id === $user->id;
  }

}
