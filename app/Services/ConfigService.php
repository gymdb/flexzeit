<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\Lesson;

/**
 * Service for accessing config option values from database while using caches
 *
 * @package App\Services
 */
interface ConfigService {

  /**
   * Return the start of the school year
   *
   * @param Date|null $min Earliest possible date
   * @return Date
   */
  public function getYearStart(Date $min = null);

  /**
   * Return the end of the school year
   *
   * @param Date|null $max Latest possible date
   * @return Date
   */
  public function getYearEnd(Date $max = null);

  /**
   * Get the first date for which courses can currently be created (included)
   *
   * @return Date
   */
  public function getFirstCourseCreateDate();

  /**
   * Get the first date for which courses can currently be created (included)
   *
   * @return Date
   */
  public function getLastCourseCreateDate();

  /**
   * Return the minimum number for a grade
   *
   * @return int
   */
  public function getMinYear();

  /**
   * Return the maximum number for a grade
   *
   * @return int
   */
  public function getMaxYear();

  /**
   * Get the number of lessons on a given date
   *
   * @param Date $date
   * @return int
   */
  public function getLessonCount(Date $date);

  /**
   * @return array
   */
  public function getLessonTimes();

  /**
   * Get an array containing all days of week with no lessons
   *
   * @return array
   */
  public function getDaysWithoutLessons();

  /**
   * Get the start time of a lesson on a given date
   *
   * @param Date $date
   * @param int $number
   * @return string|null
   */
  public function getLessonStart(Date $date, $number);

  /**
   * Get the end time of a lesson on a given date
   *
   * @param Date $date
   * @param int $number
   * @return string|null
   */
  public function getLessonEnd(Date $date, $number);

  /**
   * Get the first date for which registrations are currently possible (included)
   *
   * @return Date
   */
  public function getFirstRegisterDate();

  /**
   * Get the last date for which registrations are currently possible (included)
   *
   * @return Date
   */
  public function getLastRegisterDate();

  /**
   * Get the first date for which documentations can currently be created (included)
   *
   * @return Date
   */
  public function getFirstDocumentationDate();

  /**
   * Get the last date for which documentations can currently be created (included)
   *
   * @return Date
   */
  public function getLastDocumentationDate();

  /**
   * Get the default start date in report lists
   *
   * @return Date
   */
  public function getDefaultListStartDate();

  /**
   * Get the default end date in report lists
   *
   * @return Date
   */
  public function getDefaultListEndDate();

  /**
   * Store the start and end time of a lesson in the lesson object
   *
   * @param Lesson $lesson
   */
  public function setTime(Lesson $lesson);

  /**
   * Get the mail addresses that should receive email notifications about events
   *
   * @return string|string[]
   */
  public function getNotificationRecipients();

}