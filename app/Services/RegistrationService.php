<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Collection;

interface RegistrationService {

  /**
   * Registers a group for a complete course
   *
   * @param Course $course Course loaded from the database
   * @param Collection $students IDs of students to add to the course
   * @param RegistrationType $type The registration parameters
   * @param Date|null $firstDate Only add for lessons on or after this date
   */
  public function registerStudentsForCourse(Course $course, Collection $students, RegistrationType $type, Date $firstDate = null);

  /**
   * Registers a student for a complete course
   *
   * @param Course $course Course loaded from the database
   * @param Student $student Student loaded from the database
   * @param RegistrationType $type The registration parameters
   */
  public function registerStudentForCourse(Course $course, Student $student, RegistrationType $type);

  /**
   * @param Course $course Course loaded from the database
   * @param Student $student Student loaded from the database
   * @param RegistrationType $type The registration parameters
   * @return int|null null if validation succeeds, error code otherwise
   */
  public function validateStudentForCourse(Course $course, Student $student, RegistrationType $type);

  /**
   * Registers multiple students for a single lesson
   *
   * @param Lesson $lesson
   * @param Collection $students
   * @param RegistrationType $type The registration parameters
   */
  public function registerStudentsForLesson(Lesson $lesson, Collection $students, RegistrationType $type);

  /**
   * Registers a student for a single lesson
   *
   * @param Lesson $lesson Lesson loaded from the database
   * @param Student $student Student loaded from the database
   * @param RegistrationType $type The registration parameters
   */
  public function registerStudentForLesson(Lesson $lesson, Student $student, RegistrationType $type);

  /**
   * @param Lesson $lesson Lesson loaded from the database
   * @param Student $student Student loaded from the database
   * @param RegistrationType $type The registration parameters
   * @return int|null null if validation succeeds, error code otherwise
   */
  public function validateStudentForLesson(Lesson $lesson, Student $student, RegistrationType $type);

  /**
   * Unregisters a student from a complete course
   *
   * @param Course $course Course loaded from the database
   * @param Student $student Student loaded from the database
   * @param RegistrationType $type The registration parameters
   */
  public function unregisterStudentFromCourse(Course $course, Student $student, RegistrationType $type);

  /**
   * Unregisters a student from a single lesson
   *
   * @param Registration $registration
   * @param RegistrationType $type The registration parameters
   */
  public function unregisterStudentFromLesson(Registration $registration, RegistrationType $type);

  /**
   * Unregisters all students from a course
   *
   * @param Course $course
   * @param Date|null $firstDate Only delete registrations on or after the date
   */
  public function unregisterAllFromCourse(Course $course, Date $firstDate = null);

  /**
   * Unregisters the given students from a course
   *
   * @param Course $course
   * @param int[] $students IDs of students to unregister
   */
  public function unregisterStudentsFromCourse(Course $course, array $students);

  /**
   * Check whether registration is possible for the given date
   *
   * @param Date $value
   * @return boolean
   */
  public function isRegistrationPossible(Date $value);

  /**
   * Set attendance of one student in a given lesson
   *
   * @param Registration $registration
   * @param bool $attendance
   * @param bool $force
   */
  public function setAttendance(Registration $registration, $attendance, $force = false);

  /**
   * Set attendance on all associated registrations
   *
   * @param Lesson $lesson
   */
  public function setAttendanceChecked(Lesson $lesson);

  /**
   * @param Lesson $lesson
   * @return Collection<Registration>
   */
  public function getForLesson(Lesson $lesson);

  /**
   * @param Course $course
   * @return Collection<Registration>
   */
  public function getForCourse(Course $course);

  /**
   * @param Student $student
   * @param Date|null $date
   * @param Date|null $end
   * @return Collection<Registration>
   */
  public function getSlots(Student $student, Date $date = null, Date $end = null);

  /**
   * Get data for the list overview
   *
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Collection <array>
   */
  public function getMappedForList(Group $group, Student $student = null, Date $start = null, Date $end = null, Teacher $teacher = null,
      Subject $subject = null);

  /**
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return Collection<array>
   */
  public function getMissing(Group $group, Student $student = null, Date $start = null, Date $end = null);

  /**
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return Collection<array>
   */
  public function getMappedAbsent(Group $group, Student $student = null, Date $start = null, Date $end = null);

  /**
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return Collection<array>
   */
  public function getByTeacher(Group $group, Student $student = null, Date $start = null, Date $end = null);

  /**
   * @param Course $course
   * @param Student $student
   * @return array
   */
  public function getWarningsForCourse(Course $course, Student $student);

  /**
   * @param Lesson $lesson
   * @param Student $student
   * @return array
   */
  public function getWarningsForLesson(Lesson $lesson, Student $student);

}