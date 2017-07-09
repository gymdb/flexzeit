<?php

namespace App\Http\Requests\Course;

use App\Models\Course;

trait NormalCourseTrait {

  protected function typeSpecificRules() {
    return [
        'maxStudents' => 'nullable|bail|integer|min:1',
        'yearFrom'    => 'nullable|bail|integer|year_from:yearTo',
        'yearTo'      => 'nullable|bail|integer|year_to:yearFrom'
    ];
  }

  protected function typeSpecificPopulate(Course $course) {
    $course->maxstudents = (int)$this->input('maxStudents') ?: null;
    $course->yearfrom = (int)$this->input('yearFrom') ?: null;
    $course->yearto = (int)$this->input('yearTo') ?: null;

    return $course;
  }

}
