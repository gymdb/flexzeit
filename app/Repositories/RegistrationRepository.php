<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface RegistrationRepository {

  /**
   * @param Student|Group $student
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|int[]|null $number Only show the given lesson numbers
   * @param bool $showCancelled
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryForStudent($student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null);

  /**
   * Query registrations where no documentation was added
   *
   * @param Student|Group $student
   * @param Date $start
   * @param Date|null $end
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryDocumentation($student, Date $start, Date $end = null, Teacher $teacher = null, Subject $subject = null);

  /**
   * @param Group $group
   * @param Student|null $student
   * @param Date $start
   * @param Date $end
   * @return Builder
   */
  public function queryMissing(Group $group, Student $student = null, Date $start, Date $end);

  /**
   * @param Student $student
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function querySlots(Student $student, Date $start, Date $end = null);

  /**
   * @param Student|Group $student
   * @param Date $start
   * @param Date|null $end
   * @param null $number
   * @param bool $showCancelled
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Builder
   */
  public function queryWithExcused($student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null);

  /**
   * @param Student|Group $student
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function queryAbsent($student, Date $start, Date $end = null);

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
   * @return
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
