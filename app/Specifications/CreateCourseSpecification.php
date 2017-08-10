<?php

namespace App\Specifications;

use App\Helpers\Date;
use App\Models\Course;

interface CreateCourseSpecification {

  /**
   * @return Date
   */
  public function getFirstDate();

  /**
   * @return Date|null
   */
  public function getLastDate();

  /**
   * @return int
   */
  public function getLessonNumber();

  /**
   * @return int
   */
  public function getRoom();

  /**
   * Populate a course model with the specified data
   *
   * @param Course|null $course
   * @return Course
   */
  public function populateCourse(Course $course = null);

}