<?php

namespace App\Services;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Teacher;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface CourseService {

  /**
   * Check if a teacher can teach a course at the given time
   *
   * @param Teacher $teacher The teacher to check
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $numbers Lesson number
   * @throws CourseException Course already exists at one of the lessons
   */
  public function coursePossible(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers);

  /**
   * Check if an obligatory course is possible for the given groups within the range
   *
   * @param Builder $groups
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $numbers Lesson number
   * @throws CourseException Course already exists at one of the lessons
   */
  public function obligatoryPossible(Builder $groups, Date $firstDate, Date $lastDate = null, $numbers);

  /**
   * @param Teacher $teacher
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $numbers
   * @return mixed
   */
  public function getLessonsWithCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers);

  /**
   * Get the lessons which will be assigned to a newly created course
   *
   * @param Teacher $teacher
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $numbers Lesson number
   * @return Collection<Lesson>
   */
  public function getLessonsForCourse(Teacher $teacher, Date $firstDate, Date $lastDate = null, $numbers);

  /**
   * @param CreateCourseSpecification $spec The course information for the created course
   * @param Teacher $teacher The teacher teaching the course
   * @return Course
   * @throws CourseException
   */
  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher);

  public function editCourse(EditCourseSpecification $course);

  public function removeCourse(Course $course);

  public function getFirstCreateDate();

  public function getLastCreateDate();

  public function getMinYear();

  public function getMaxYear();

}