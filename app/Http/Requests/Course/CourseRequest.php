<?php

namespace App\Http\Requests\Course;

use App\Helpers\Date;
use App\Models\Course;
use DateTime;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class CourseRequest extends FormRequest {

  private $parsed = false;

  protected $dateFields = ['lastDate'];

  /**
   * @return array
   */
  abstract protected function typeSpecificRules();

  /**
   * @param Course $course
   * @return Course
   */
  abstract protected function typeSpecificPopulate(Course $course);

  public function authorize() {
    // Authorization is done by controller
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    return array_merge([
        'name'        => 'required|string|max:50',
        'description' => 'nullable|string',
        'room'        => 'required|string|max:50',
        'lastDate'    => 'nullable|bail|date|after:today|in_year'
    ], $this->typeSpecificRules());
  }

  /**
   * @return Date|null
   */
  public function getLastDate() {
    $input = $this->input('lastDate');
    return $input instanceof Date ? $input : null;
  }

  /**
   * Populate a course model with the specified data
   *
   * @param Course $course
   * @return Course
   */
  public function populateCourse(Course $course) {
    $course->name = $this->input('name');
    $course->description = $this->input('description') ?: "";
    $course->room = $this->input('room');

    return $this->typeSpecificPopulate($course);
  }

  protected function validationData() {
    $this->parseParameters();
    return parent::validationData();
  }

  protected function parseParameters() {
    if (!$this->parsed) {
      $this->parsed = true;
      $this->parse($this->getInputSource());
    }
  }

  protected function parse(ParameterBag $source) {
    foreach ($this->dateFields as $field) {
      if ($source->has($field) && ($value = $source->get($field)) && ($date = Date::checkedCreate($value))) {
        $source->set($field, $date);
      }
    }
  }

}
