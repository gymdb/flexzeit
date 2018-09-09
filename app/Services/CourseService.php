<?php

namespace App\Services;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Specifications\CreateCourseSpecification;
use App\Specifications\EditCourseSpecification;
use Illuminate\Support\Collection;

interface CourseService {

  /**
   * Save a new course to the database
   *
   * @param CreateCourseSpecification $spec The course information for the created course
   * @param Teacher $teacher The teacher teaching the course
   * @return Course
   * @throws CourseException
   */
  public function createCourse(CreateCourseSpecification $spec, Teacher $teacher);

  /**
   * Modify an already existing course
   *
   * @param EditCourseSpecification $spec
   * @param Course $course
   * @return Course
   * @throws CourseException
   */
  public function editCourse(EditCourseSpecification $spec, Course $course);

  /**
   * @param Course $course
   */
  public function removeCourse(Course $course);

  /**
   * @param Teacher $teacher
   * @param DateConstraints $constraints
   * @param array|null $groups
   * @return array
   */
  public function getDataForCreate(Teacher $teacher, DateConstraints $constraints, array $groups = null);

  /**
   * @param Course $course
   * @param Date|null $lastDate
   * @param array|null $groups
   * @return array
   */
  public function getDataForEdit(Course $course, Date $lastDate = null, array $groups = null);

  /**
   * Get a list of a teacher's courses
   *
   * @param Teacher $teacher
   * @param DateConstraints $constraints
   * @return Collection<array>
   */
  public function getMappedForTeacher(Teacher $teacher = null, DateConstraints $constraints);

  /**
   * @param Group|null $group
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param DateConstraints $constraints
   * @return Collection<array>
   */
  public function getMappedObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, DateConstraints $constraints);

  /**
   * Get a list of a courses available for a student
   *
   * @param Student $student
   * @param Teacher $teacher
   * @param DateConstraints $constraints
   * @return Collection<array>
   */
  public function getMappedForStudent(Student $student, Teacher $teacher = null, DateConstraints $constraints);
}
