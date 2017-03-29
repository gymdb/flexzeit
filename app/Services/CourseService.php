<?php

namespace App\Services;

use App\Exceptions\CourseException;
use App\Models\Course;
use App\Models\Teacher;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use Carbon\Carbon;

interface CourseService {

  /**
   * Check if creating a course within the given time range is possible
   *
   * @param Carbon $firstDate
   * @param Carbon|null $lastDate
   * @throws CourseException
   */
  public function validateDates(Carbon $firstDate, Carbon $lastDate = null);

  /**
   * Check if a teacher can teach a course at the given time
   *
   * @param Teacher $teacher The teacher to check
   * @param Carbon $firstDate
   * @param Carbon|null $lastDate
   * @param array $numbers Lesson numbers
   * @throws CourseException Course already exists at one of the lessons
   */
  public function coursePossible(Teacher $teacher, Carbon $firstDate, Carbon $lastDate = null, array $numbers);

  /**
   * Check if an obligatory course is possible for the given groups within the range
   *
   * @param int[] $groups
   * @param Carbon $firstDate
   * @param Carbon|null $lastDate
   * @param array $numbers Lesson numbers
   * @throws CourseException Course already exists at one of the lessons
   */
  public function obligatoryPossible(array $groups, Carbon $firstDate, Carbon $lastDate = null, array $numbers);

  /**
   * @param CreateCourseSpecification $spec The course information for the created course
   * @param Teacher $teacher The teacher teaching the course
   * @throws CourseException
   */
  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher);

  public function editCourse(EditCourseSpecification $course);

  public function removeCourse(Course $course);

}