<?php

namespace App\Http\Requests\Course;

use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Support\Facades\App;

trait NormalCourseTrait {

  protected function typeSpecificRules() {
    /** @var CourseService $courseService */
    $courseService = App::make(CourseService::class);

    $yearFrom = (int)$this->input('yearFrom');
    $yearTo = (int)$this->input('yearTo');
    $minYear = $courseService->getMinYear();
    $maxYear = $courseService->getMaxYear();
    $maxYearFrom = $yearTo ? min($yearTo, $maxYear) : $maxYear;
    $minYearTo = $yearFrom ? max($yearFrom, $minYear) : $minYear;

    return [
        'maxStudents' => 'nullable|integer|min:1',
        'yearFrom'    => 'nullable|integer|between:' . $minYear . ',' . $maxYearFrom,
        'yearTo'      => 'nullable|integer|between:' . $minYearTo . ',' . $maxYear
    ];
  }

  protected function typeSpecificPopulate(Course $course) {
    $course->maxstudents = (int)$this->input('maxStudents') ?: null;
    $course->yearfrom = (int)$this->input('yearFrom') ?: null;
    $course->yearto = (int)$this->input('yearTo') ?: null;

    return $course;
  }

}