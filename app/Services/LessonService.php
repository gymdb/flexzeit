<?php

namespace App\Services;

use App\Exceptions\LessonException;
use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

interface LessonService {

  /**
   * @param Lesson $lesson
   * @throws LessonException
   */
  public function cancelLesson(Lesson $lesson);

  /**
   * @param Lesson $lesson
   * @throws LessonException
   */
  public function reinstateLesson(Lesson $lesson);

  /**
   * @param Teacher $teacher
   * @param DateConstraints $constraints
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Collection<array>
   */
  public function getMappedForTeacher(Teacher $teacher, DateConstraints $constraints, $showCancelled = false, $withCourse = false);

  /**
   * Get lessons held by a teacher on a given day
   *
   * @param Teacher $teacher
   * @param Date|null $date Date of lessons, today if null
   * @return Collection<Lesson>
   */
  public function getForDay(Teacher $teacher, Date $date = null);

  /**
   * Get lessons associated with a course
   *
   * @param Course $course
   * @return Collection<Lesson>
   */
  public function getForCourse(Course $course);

  /**
   * @param Student $student
   * @param DateConstraints $constraints
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param string|null $type
   * @return Collection <array>
   */
  public function getAvailableLessons(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null, $type = null);

  /**
   * Checks if attendance has been checked for the given lesson
   *
   * @param Lesson $lesson
   * @return boolean
   */
  public function isAttendanceChecked(Lesson $lesson);

  /**
   * @param Lesson $lesson
   * @return bool
   */
  public function hasRegistrationsWithoutDuplicates(Lesson $lesson);

  /**
   * @param Lesson $lesson
   * @param Teacher $teacher
   * @throws LessonException
   * @return array
   */
  public function getSubstituteInformation(Lesson $lesson, Teacher $teacher);

  /**
   * @param Lesson $lesson
   * @param Teacher $teacher
   * @param int|null $untisId
   * @param bool $updateRoom
   * @throws LessonException
   */
  public function substituteLesson(Lesson $lesson, Teacher $teacher, $untisId = null, $updateRoom = false);

}
