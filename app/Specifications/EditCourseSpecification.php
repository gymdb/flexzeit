<?php

namespace App\Specifications;

use App\Models\Course;
use Carbon\Carbon;

interface EditCourseSpecification {

  /**
   * @return int
   */
  public function getId();

  /**
   * @return Carbon|null
   */
  public function getLastDate();

  /**
   * Populate a course model with the specified data
   *
   * @param Course $course
   * @return Course
   */
  public function populateCourse(Course $course);

}