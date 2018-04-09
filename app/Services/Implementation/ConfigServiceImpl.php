<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Lesson;
use App\Services\ConfigService;
use App\Services\ConfigStorageService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Service for accessing config option values from database while using caches
 *
 * @package App\Services
 */
class ConfigServiceImpl implements ConfigService {

  /** @var ConfigStorageService */
  private $configService;

  /** @var string Cache prefix for unlimited cached data */
  private $prefix = 'parsed';

  /** @var int Duration in minutes for daily updated cache entries */
  private $cacheDuration;

  function __construct(ConfigStorageService $configService) {
    $this->configService = $configService;
    $this->cacheDuration = Carbon::now()->secondsUntilEndOfDay() / 60;
  }

  public function getYearStart(Date $min = null) {
    $date = $this->getCache()->rememberForever($this->prefix . '.year.start', function() {
      return $this->configService->getAsDate('year.start');
    });
    return $min ? max($date, $min) : $date;
  }

  public function getYearEnd(Date $max = null) {
    $date = $this->getCache()->rememberForever($this->prefix . '.year.end', function() {
      return $this->configService->getAsDate('year.end');
    });
    return $max ? min($date, $max) : $date;
  }

  public function getFirstCourseCreateDate() {
    return $this->getCache()->remember($this->prefix . '.course.create', $this->cacheDuration, function() {
      return $this->getYearStart($this->getDateBound('course.create'));
    });
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

  public function getLessonCount(Date $date) {
    return count($this->getLessonTimes()[$date->dayOfWeek]);
  }

  public function getLessonTimes() {
    return $this->configService->get('lessons', []);
  }

  public function getDaysWithoutLessons() {
    return $this->getCache()->rememberForever($this->prefix . '.daysWithoutLessons', function() {
      $lessons = $this->getLessonTimes();
      $daysWithoutLessons = [];
      for ($d = 0; $d < 7; $d++) {
        if (empty($lessons[$d])) {
          $daysWithoutLessons[] = $d;
        }
      }
      return $daysWithoutLessons;
    });
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
    return $this->getCache()->remember($this->prefix . '.registration.first', $this->cacheDuration, function() {
      return $this->getDateBound('registration.end');
    });
  }

  public function getLastRegisterDate() {
    return $this->getCache()->remember($this->prefix . '.registration.last', $this->cacheDuration, function() {
      return $this->getDateBound('registration.begin')->addDay(-1);
    });
  }

  public function getFirstDocumentationDate() {
    return $this->getCache()->remember($this->prefix . '.documentation.first', $this->cacheDuration, function() {
      return $this->getDateBound('documentation', -1);
    });
  }

  public function getLastDocumentationDate() {
    return Date::today();
  }

  public function getDefaultListStartDate(Date $max = null) {
    if ($max) {
      return $this->getYearStart(min($max, Date::today()));
    }

    return $this->getCache()->remember($this->prefix . '.list.start', $this->cacheDuration, function() {
      return $this->getYearStart(Date::today());
    });
  }

  public function getDefaultListEndDate(Date $min = null) {
    if ($min) {
      return $this->getYearStart(max($min, Date::today()->addWeek(1)));
    }

    return $this->getCache()->remember($this->prefix . '.list.end', $this->cacheDuration, function() {
      return $this->getYearEnd(Date::today()->addWeek(1));
    });
  }

  public function setTime($lesson) {
    $lesson->time = [
        'number' => $lesson->number
//        'start' => $this->getLessonStart($lesson->date, $lesson->number),
//        'end'   => $this->getLessonEnd($lesson->date, $lesson->number)
    ];
  }

  public function getNotificationRecipients() {
    return $this->configService->get('notification.recipients', []);
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

  private function getCache() {
    return Cache::getFacadeRoot();
  }

}
