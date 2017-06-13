<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
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
   * Registers a student for a complete course
   *
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
   * Give feedback for a particular registration
   *
   * @param Registration $registration
   * @param string $feedback
   */
  public function setFeedback(Registration $registration, $feedback);

  /**
   * Set attendance of one student in a given lesson
   *
   * @param Registration $registration
   * @param bool $attendance
   */
  public function setAttendance(Registration $registration, $attendance);

  /**
   * Set attendance on all associated registrations
   *
   * @param Lesson $lesson
   */
  public function setAttendanceChecked(Lesson $lesson);

  /**
   * Add documentation for a lesson
   *
   * @param Registration $registration Registration loaded from the database
   * @param string $documentation
   */
  public function documentLesson(Registration $registration, $documentation);

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

}