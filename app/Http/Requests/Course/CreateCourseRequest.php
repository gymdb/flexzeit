<?php

namespace App\Http\Requests\Course;

use App\Models\Course;
use App\Specifications\CreateCourseSpecification;
use Carbon\Carbon;

abstract class CreateCourseRequest extends CourseRequest implements CreateCourseSpecification {

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
        'firstDate'   => 'required|date|after:today',
        'lastDate'    => 'nullable|date|after_or_equal:firstDate',
        'firstLesson' => 'nullable|integer|min:1',
        'lastLesson'  => 'nullable|integer|min:1'
    ]);
  }

  /**
   * @return Carbon|null
   */
  public function getFirstDate() {
    return ($input = $this->input('firstDate')) ? Carbon::parse($input) : null;
  }

  /**
   * @return int
   */
  public function getFirstLesson() {
    return $this->input('firstLesson', 1);
  }

  /**
   * @return int|null
   */
  public function getLastLesson() {
    return $this->input('lastLesson');
  }

  /**
   * Populate a course model with the specified data
   *
   * @param Course|null $course
   * @return Course
   */
  public final function populateCourse(Course $course = null) {
    return parent::populateCourse($course ?: new Course());
  }

}
