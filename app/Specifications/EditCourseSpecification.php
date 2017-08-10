<?php

namespace App\Specifications;

use App\Helpers\Date;
use App\Models\Course;

interface EditCourseSpecification {

  /**
   * @return Date|null
   */
  public function getLastDate();

  /**
   * @return int
   */
  public function getRoom();

  /**
   * Populate a course model with the specified data
   *
   * @param Course $course
   * @return Course
   */
  public function populateCourse(Course $course);

}