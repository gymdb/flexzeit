<?php

namespace App\Specifications;

use App\Helpers\Date;
use App\Models\Course;

interface EditCourseSpecification {

  /**
   * @return int
   */
  public function getId();

  /**
   * @return Date|null
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