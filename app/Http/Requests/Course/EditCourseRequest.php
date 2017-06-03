<?php

namespace App\Http\Requests\Course;

use App\Specifications\EditCourseSpecification;

abstract class EditCourseRequest extends CourseRequest implements EditCourseSpecification {

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    return array_merge(parent::rules(), [
        'id' => 'required|integer|exists:course'
    ]);
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->input('id');
  }

}
