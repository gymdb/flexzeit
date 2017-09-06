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
  public function showRegistrations(User $user, Student $student = null) {
    if (!$user->isTeacher()) {
      return false;
    }
    /** @var Teacher $user */
    if ($user->admin) {
      return true;
    }
    return $this->checkForTeacher($user, $student);
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
    /** @var Teacher $user */
    if ($user->admin) {
      return true;
    }
    return $this->checkForTeacher($user, $student);
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
    /** @var Teacher $user */
    return $this->checkForTeacher($user, $student);
  }

  private function checkForTeacher(Teacher $teacher, Student $student = null) {
    return $teacher->form && (!$student || $teacher->form->students()->wherePivot('student_id', $student->id)->exists());
  }

}
