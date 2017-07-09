<?php

namespace App\Services;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use Illuminate\Support\Collection;

interface CourseService {

  /**
   * Check if a teacher can teach a course at the given time
   *
   * @param Teacher $teacher The teacher to check
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $number Lesson number
   * @throws CourseException Course already exists at one of the lessons
   */
  public function coursePossible(Teacher $teacher, Date $firstDate, Date $lastDate = null, $number);

  /**
   * Check if an obligatory course is possible for the given groups within the range
   *
   * @param int[] $groups
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $number Lesson number
   * @param Course|null $exclude
   * @return  Course already exists at one of the lessons
   */
  public function obligatoryPossible(array $groups, Date $firstDate, Date $lastDate = null, $number, Course $exclude = null);

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
   * Return all lessons where one of the given groups already has a course
   *
   * @param array $groups
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[] $number
   * @param Course|null $exclude
   * @return Collection <Lesson>
   */
  public function getLessonsWithObligatory(array $groups, Date $firstDate, Date $lastDate = null, $number, Course $exclude = null);

  /**
   * @param CreateCourseSpecification $spec The course information for the created course
   * @param Teacher $teacher The teacher teaching the course
   * @return Course
   * @throws CourseException
   */
  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher);

  public function editCourse(EditCourseSpecification $spec, Course $course);

  /**
   * Get the first modified date for an edit request
   *
   * @param Date $oldLastDate
   * @param Date|null $lastDate
   * @return Date|null
   */
  public function getFirstChanged(Date $oldLastDate, Date $lastDate = null);

  /**
   * @param Course $course
   */
  public function removeCourse(Course $course);

  /**
   * Get a list of a teacher's courses
   *
   * @param Teacher $teacher
   * @param Date $start
   * @param Date|null $end
   * @return Collection<array>
   */
  public function getMappedForTeacher(Teacher $teacher, Date $start, Date $end = null);

  /**
   * @param Group|null $group
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param Date $start
   * @param Date|null $end
   * @return Collection<array>
   */
  public function getMappedObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start, Date $end = null);

  /**
   * Get the lessons which will be removed from the course
   *
   * @param Course $course
   * @param Date $firstRemoved
   * @return Collection <array>
   */
  public function getMappedRemovedLessons(Course $course, Date $firstRemoved);

}