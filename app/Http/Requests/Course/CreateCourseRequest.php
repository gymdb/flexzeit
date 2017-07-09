<?php

namespace App\Http\Requests\Course;

use App\Helpers\Date;
use App\Models\Course;
use App\Specifications\CreateCourseSpecification;

abstract class CreateCourseRequest extends CourseRequest implements CreateCourseSpecification {

  protected $dateFields = ['firstDate', 'lastDate'];

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    return array_merge(parent::rules(), [
        'firstDate'    => 'required|bail|date|create_allowed|school_day',
        'lastDate'     => 'nullable|bail|date|after_or_equal:firstDate|create_allowed',
        'lessonNumber' => 'required|bail|integer|min:1|lesson_number:firstDate'
    ]);
  }

  /**
   * @return Date|null
   */
  public function getFirstDate() {
    $input = $this->input('firstDate');
    return $input instanceof Date ? $input : null;
  }

  /**
   * @return int[]|null
   */
  public function getLessonNumber() {
    return $this->input('lessonNumber');
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
