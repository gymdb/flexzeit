<?php

namespace App\Http\Requests\Course;

use App\Helpers\Date;
use App\Models\Course;
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
        'room'        => 'required|integer|exists:rooms,id',
        'lastDate'    => 'nullable|bail|date|edit_allowed'
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
   * @return int
   */
  public function getRoom() {
    return $this->input('room');
  }

  /**
   * Populate a course model with the specified data
   *
   * @param Course $course
   * @return Course
   */
  public function populateCourse(Course $course) {
    $course->name = $this->input('name');
    $course->description = $this->input('description') ?: '';

    return $this->typeSpecificPopulate($course);
  }

  protected function validationData() {
    $this->parse($this->getInputSource());
    return parent::validationData();
  }

  private function parse(ParameterBag $source) {
    if (!$this->parsed) {
      $this->parsed = true;
      foreach ($this->dateFields as $field) {
        if ($source->has($field) && ($value = $source->get($field)) && ($date = Date::checkedCreate($value))) {
          $source->set($field, $date);
        }
      }
    }
  }

}
