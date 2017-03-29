<?php

namespace App\Http\Requests\Course;

use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

abstract class CourseRequest extends FormRequest {

  /**
   * @return array
   */
  abstract protected function typeSpecificRules();

  /**
   * @param Course $course
   * @return Course
   */
  abstract protected function typeSpecificPopulate(Course $course);

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    return array_merge([
        'name'        => 'required|string|max:255',
        'description' => 'nullable|string',
        'room'        => 'required|string|max:255',
        'lastDate'    => 'nullable|date|after:today'
    ], $this->typeSpecificRules());
  }

  /**
   * @return Carbon|null
   */
  public function getLastDate() {
    return ($input = $this->input('lastDate')) ? Carbon::parse($input) : null;
  }

  /**
   * Populate a course model with the specified data
   *
   * @param Course $course
   * @return Course
   */
  public function populateCourse(Course $course) {
    $course->name = $this->input('name');
    $course->description = $this->input('description');
    $course->room = $this->input('room');

    return $this->typeSpecificPopulate($course);
  }

}
