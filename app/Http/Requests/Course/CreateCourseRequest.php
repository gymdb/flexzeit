<?php

namespace App\Http\Requests\Course;

use App\Helpers\Date;
use App\Models\Course;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Specifications\CreateCourseSpecification;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class CreateCourseRequest extends CourseRequest implements CreateCourseSpecification {

  /** @var int */
  private $lessonCount;

  protected $dateFields = ['firstDate', 'lastDate'];

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    $this->parseParameters();
    return array_merge(parent::rules(), [
        'firstDate'    => 'required|bail|date|after:today|in_year|create_allowed|school_day',
        'lastDate'     => 'nullable|bail|date|after_or_equal:firstDate|in_year',
        'lessonNumber' => 'required|integer|' . ($this->lessonCount ? 'between:1,' . $this->lessonCount : 'min:1')
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

  protected function parse(ParameterBag $source) {
    parent::parse($source);

    $this->lessonCount = ($firstDate = $this->getFirstDate())
        ? App::make(LessonService::class)->getLessonCount($firstDate) : 0;
  }

}
