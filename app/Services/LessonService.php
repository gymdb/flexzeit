<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

interface LessonService {

  /**
   * @param Teacher $teacher
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|null $numbers Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @return Collection
   */
  public function getForTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false, $withCourse = false);

  /**
   * Get lessons held by a teacher on a given day
   *
   * @param Teacher $teacher
   * @param Date|null $date Date of lessons, today if null
   * @return Collection
   */
  public function getForDay(Teacher $teacher, Date $date = null);

  /**
   * Get lessons associated with a course
   *
   * @param Course $course
   * @return Collection
   */
  public function getForCourse(Course $course);

  /**
   * Get the number of lessons on a given date
   *
   * @param Date $date
   * @return int
   */
  public function getLessonCount(Date $date);

  /**
   * @return mixed
   */
  public function getAllLessonTimes();

  /**
   * Get an array containing all days
   *
   * @param array|null $lessons Previously loaded number of lessons for each day of week
   * @return mixed
   */
  public function getDaysWithoutLessons($lessons = null);

  /**
   * Get the start time of a lesson on a given date
   *
   * @param Date $date
   * @param int $number
   * @return string|null
   */
  public function getStart(Date $date, $number);

  /**
   * Get the end time of a lesson on a given date
   *
   * @param Date $date
   * @param int $number
   * @return string|null
   */
  public function getEnd(Date $date, $number);

  /**
   * Checks if attendance has been checked for the given lesson
   *
   * @param Lesson $lesson
   * @return boolean
   */
  public function isAttendanceChecked(Lesson $lesson);

  /**
   * Stores the start and end time of the lesson in the model
   *
   * @param Lesson $lesson
   */
  public function setTimes(Lesson $lesson);

}