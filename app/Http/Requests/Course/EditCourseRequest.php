<?php

namespace App\Http\Requests\Course;

use App\Specifications\EditCourseSpecification;

abstract class EditCourseRequest extends CourseRequest implements EditCourseSpecification {

  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize() {
    // TODO Implement authorization
    return false;
  }

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
