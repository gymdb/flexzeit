<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy {

  use HandlesAuthorization;

  public function before(User $user) {
    if ($user->isTeacher() && $user->admin) {
      return true;
    }
    return null;
  }

  /**
   * Determine whether the user can view the course.
   *
   * @param  User $user
   * @param  Course $course
   * @return bool
   */
  public function view(User $user, Course $course) {
    return $this->checkUser($user, $course);
  }

  /**
   * Determine whether the user can create courses.
   *
   * @param  User $user
   * @return bool
   */
  public function create(User $user) {
    return $user->isTeacher();
  }

  /**
   * Determine whether the user can update the course.
   *
   * @param  User $user
   * @param  Course $course
   * @return bool
   */
  public function update(User $user, Course $course) {
    return $this->checkUser($user, $course);
  }

  /**
   * Determine whether the user can delete the course.
   *
   * @param  User $user
   * @param  Course $course
   * @return bool
   */
  public function delete(User $user, Course $course) {
    return $this->checkUser($user, $course);
  }

  /**
   * Determine whether the user can list obligatory courses
   *
   * @param User $user
   * @return bool
   */
  public function listObligatory(/** @noinspection PhpUnusedParameterInspection */
      User $user) {
    return false;
  }

  private function checkUser(User $user, Course $course) {
    if (!$user->isTeacher()) {
      return false;
    }
    $lesson = $course->lessons->first();
    return $lesson && $lesson->teacher_id === $user->id;
  }

}
