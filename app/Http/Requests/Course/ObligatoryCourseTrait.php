<?php

namespace App\Http\Requests\Course;

use App\Models\Course;

trait ObligatoryCourseTrait {

  protected function typeSpecificRules() {
    return [
        'groups'   => 'required|array',
        'groups.*' => 'required|integer|exists:groups,id',
        'subject'  => 'required|integer|exists:subjects,id'
    ];
  }

  protected function typeSpecificPopulate(Course $course) {
    return $course;
  }

  /**
   * @return int
   */
  public function getSubject() {
    return $this->input('subject');
  }

  /**
   * @return int[]
   */
  public function getGroups() {
    return $this->input('groups');
  }

}