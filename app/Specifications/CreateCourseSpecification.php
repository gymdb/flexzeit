<?php

namespace App\Specifications;

use App\Models\Course;
use Carbon\Carbon;

interface CreateCourseSpecification {

  /**
   * @return Carbon
   */
  public function getFirstDate();

  /**
   * @return Carbon|null
   */
  public function getLastDate();

  /**
   * @return int
   */
  public function getFirstLesson();

  /**
   * @return int|null
   */
  public function getLastLesson();

  /**
   * Populate a course model with the specified data
   *
   * @param Course|null $course
   * @return Course
   */
  public function populateCourse(Course $course = null);

}