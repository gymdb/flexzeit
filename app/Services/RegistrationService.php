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
   * @param Group $group Group loaded from the database
   */
  public function registerGroupForCourse(Course $course, Group $group);

  /**
   * Registers a student for a complete course
   *
   * @param Course $course Course loaded from the database
   * @param Student $student Student loaded from the database
   * @param bool $force Ignores course restrictions (year, max participants, assigned groups)
   * @param bool $admin Ignores all restrictions (student already registered somewhere else, course already happened)
   */
  public function registerStudentForCourse(Course $course, Student $student, $force = false, $admin = false);

  /**
   * @param Course $course Course loaded from the database
   * @param Student $student Student loaded from the database
   * @param bool $ignoreDate Don't check if date is within registration period
   * @param bool $force Ignores course restrictions (year, max participants, assigned groups)
   * @return int 0 if validation succeeds, error code otherwise
   */
  public function validateStudentForCourse(Course $course, Student $student, $ignoreDate = false, $force = false);

  /**
   * Registers a student for a single lesson
   *
   * @param Lesson $lesson Lesson loaded from the database
   * @param Student $student Student loaded from the database
   * @param bool $force Ignores course restrictions (year, max participants, assigned groups)
   * @param bool $admin Ignores all restrictions (student already registered somewhere else, course already happened)
   */
  public function registerStudentForLesson(Lesson $lesson, Student $student, $force = false, $admin = false);

  /**
   * @param Lesson $lesson
   * @param Student $student
   * @param bool $ignoreDate
   * @param bool $force
   * @return int
   */
  public function validateStudentForLesson(Lesson $lesson, Student $student, $ignoreDate = false, $force = false);

  /**
   * Unregisters a group from a complete course
   *
   * @param Course $course Course loaded from the database
   * @param Group $group Group loaded from the database
   */
  public function unregisterGroupFromCourse(Course $course, Group $group);

  /**
   * Unregisters a student from a complete course
   *
   * @param Course $course Course loaded from the database
   * @param Student $student Student loaded from the database
   * @param bool $force Also unregister from obligatory courses and after registration period ended
   */
  public function unregisterStudentFromCourse(Course $course, Student $student, $force = false);

  /**
   * Unregisters a student from a single lesson
   *
   * @param Registration $registration
   * @param bool $force Also unregister from obligatory courses and after registration period ended
   * @return
   */
  public function unregisterStudentFromLesson(Registration $registration, $force = false);

  /**
   * Unregisters all students from a single lesson
   *
   * @param Lesson $lesson Lesson loaded from the database
   */
  public function unregisterAllFromLesson(Lesson $lesson);

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
   * @return Collection
   */
  public function getForLesson(Lesson $lesson);

  /**
   * @param Course $course
   * @return Collection
   */
  public function getForCourse(Course $course);

  /**
   * Get registrations for a student
   *
   * @param Student $student
   * @param Date|null $start
   * @param Date|null $end
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Collection<Registration>
   */
  public function getForStudent(Student $student, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null);

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
   * @param Student $student
   * @param Date|null $start
   * @param Date|null $end
   * @param int|int[]|null $number
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return Collection<array>
   */
  public function getMappedForList(Student $student, Date $start = null, Date $end = null, $number = null, Teacher $teacher = null, Subject $subject = null);

  /**
   * Get the registrations a student has for a given slot
   *
   * @param Student $student
   * @param Date $date
   * @param $number
   * @return Collection<array>
   */
  public function getMappedForSlot(Student $student, Date $date, $number);

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
  public function getAbsent(Group $group, Student $student = null, Date $start = null, Date $end = null);

}