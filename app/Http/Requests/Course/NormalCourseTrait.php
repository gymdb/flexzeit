<?php

namespace App\Http\Requests\Course;

use App\Models\Course;

trait NormalCourseTrait {

  protected function typeSpecificRules() {
    return [
        'maxstudents' => 'required|integer|min:1',
        'yearfrom'    => 'nullable|integer|min:1',
        'yearto'      => 'nullable|integer|min:1'
    ];
  }

  protected function typeSpecificPopulate(Course $course) {
    $course->maxstudents = (int)$this->input('maxstudents') ?: null;
    $course->yearfrom = (int)$this->input('yearfrom') ?: null;
    $course->yearto = (int)$this->input('yearto') ?: null;

    return $course;
  }

}