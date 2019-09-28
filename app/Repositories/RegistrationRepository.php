<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface RegistrationRepository {

  /**
   * @param Student|Group|null $student
   * @param DateConstraints $constraints
   * @param bool $showCancelled
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryForStudent($student = null, DateConstraints $constraints, $showCancelled = false, Teacher $teacher = null, Subject $subject = null);



  /**
   * @param DateConstraints $constraints
   * @param bool $showCancelled
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryForAllStudents( DateConstraints $constraints, $showCancelled = false, Teacher $teacher = null, Subject $subject = null);

  /**
   * Query registrations where no documentation was added
   *
   * @param Student|Group $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryDocumentation($student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null);

  /**
   * @param Group|null $group
   * @param Student|null $student
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function queryMissing(Group $group = null, Student $student = null, DateConstraints $constraints);

  /**
   * @param Lesson $lesson
   * @param Collection $groups
   * @return Builder
   */
  public function queryMissingForLesson(Lesson $lesson, Collection $groups);

  /**
   * @param Student|Group|null $student
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function queryByTeacher($student = null, DateConstraints $constraints);

  /**
   * @param Student $student
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function querySlots(Student $student, DateConstraints $constraints);

  /**
   * @param Student|Group $student
   * @param DateConstraints $constraints
   * @param bool $showCancelled
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryWithExcused($student, DateConstraints $constraints, $showCancelled = false, Teacher $teacher = null, Subject $subject = null);

  /**
   * @param Student|Group $student
   * @param DateConstraints $constraints
   * @return Builder
   */
  public function queryAbsent($student, DateConstraints $constraints);

  /**
   * @param Relation $registrations
   * @return Builder
   */
  public function queryOrdered(Relation $registrations);

  /**
   * Find registrations at the same time as the given registrations
   *
   * @param int[] $lessons IDs of lessons whose times should be checked
   * @param array $students IDs of students to check
   * @return Builder
   */
  public function queryForLessons(array $lessons, array $students);

  /**
   * Find already existing registrations for the given combination of lessons and students
   *
   * @param array $lessons
   * @param array $students
   */
  public function queryExisting(array $lessons, array $students);

  /**
   * Unregisters all students from a course
   *
   * @param Course $course
   * @param Date|null $firstDate Only delete registrations on or after the date
   * @param int[]|null $students IDs of students to delete from registrations
   */
  public function deleteForCourse(Course $course, Date $firstDate = null, array $students = null);

  /**
   * @param int[] $lessons IDs of lessons for which registrations should be deleted
   * @param int[] $students IDs of students for which registrations should be deleted
   */
  public function deleteForLessons(array $lessons, array $students);

  /**
   * Delete registrations for the given lesson where there is another registration at the same time
   *
   * @param Lesson $lesson
   */
  public function deleteDuplicate(Lesson $lesson);

  /**
   * Get all registrations for which there is no other registration at the same time
   *
   * @param Lesson $lesson
   * @param bool $invert
   * @return Builder
   */
  public function queryNoneDuplicateRegistrations(Lesson $lesson, $invert = false);

}
