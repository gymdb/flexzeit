<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Lesson;
use App\Services\ConfigService;
use App\Services\ConfigStorageService;

/**
 * Service for accessing config option values from database while using caches
 *
 * @package App\Services
 */
class ConfigServiceImpl implements ConfigService {

  /** @var ConfigStorageService */
  private $configService;

  function __construct(ConfigStorageService $configService) {
    $this->configService = $configService;
  }

  public function getYearStart(Date $min = null) {
    $date = $this->configService->getAsDate('year.start');
    return $min ? max($date, $min) : $date;
  }

  public function getYearEnd(Date $max = null) {
    $date = $this->configService->getAsDate('year.end');
    return $max ? min($date, $max) : $date;
  }

  public function getFirstCourseCreateDate() {
    return $this->getYearStart($this->getDateBound('course.create'));
  }

  public function getLastCourseCreateDate() {
    return $this->getYearEnd();
  }

  public function getMinYear() {
    return $this->configService->getAsInt('year.min', 1);
  }

  public function getMaxYear() {
    return $this->configService->getAsInt('year.max', 1);
  }

  public function getMaxStudents() {
    return $this->configService->getAsInt("maxstudents", 0);
  }

  public function getLessonCount(Date $date) {
    return count($this->getLessonTimes()[$date->dayOfWeek]);
  }

  public function getLessonTimes() {
    return $this->configService->get('lessons', []);
  }

  public function getDaysWithoutLessons($lessons = null) {
    $lessons = $this->getLessonTimes();
    $daysWithoutLessons = [];
    for ($d = 0; $d < 7; $d++) {
      if (empty($lessons[$d])) {
        $daysWithoutLessons[] = $d;
      }
    }
    return $daysWithoutLessons;
  }

  public function getLessonStart(Date $date, $number) {
    $lessons = $this->getLessonTimes();
    return isset($lessons[$date->dayOfWeek], $lessons[$date->dayOfWeek][$number]) ? $lessons[$date->dayOfWeek][$number]['start'] : null;
  }

  public function getLessonEnd(Date $date, $number) {
    $lessons = $this->getLessonTimes();
    return isset($lessons[$date->dayOfWeek], $lessons[$date->dayOfWeek][$number]) ? $lessons[$date->dayOfWeek][$number]['end'] : null;
  }

  public function getFirstRegisterDate() {
    return $this->getDateBound('registration.end');
  }

  public function getLastRegisterDate() {
    return $this->getDateBound('registration.begin')->addDay(-1);
  }

  public function getFirstDocumentationDate() {
    return $this->getDateBound('documentation', -1)->addDay(-1);
  }

  public function getLastDocumentationDate() {
    return Date::today();
  }

  public function getDefaultListStartDate() {
    return max(Date::today()->addWeek(-1), $this->getYearStart());
  }

  public function getDefaultListEndDate() {
    return min(Date::today()->addWeek(1), $this->getYearEnd());
  }

  public function setTime(Lesson $lesson) {
    $lesson->time = [
        'start' => $this->getLessonStart($lesson->date, $lesson->number),
        'end'   => $this->getLessonEnd($lesson->date, $lesson->number)
    ];
  }

  /**
   * Return the date boundary (earliest or last possible date for some action) specified by a day/week config pair
   *
   * @param string $key
   * @param int $future 1 for bound in future, -1 for bound in past
   * @return Date
   */
  private function getDateBound($key, $future = 1) {
    $day = $this->configService->getAsInt($key . '.day');
    if (is_null($day)) {
      $day = 1;
      $week = 0;
    } else {
      $week = $this->configService->getAsInt($key . '.week', 0);
    }

    $today = Date::today();
    return $week <= 0
      // Allow creation up to given days before the date
        ? $today->addDays($future * $day)
      // Allow creation up to given weeks before the date, in relation to the start of the week for the given day of week
        : $today->setToDayOfWeek($day)->startOfWeek()->addWeeks($future * $week);
  }

}