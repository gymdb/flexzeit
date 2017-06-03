<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Repositories\LessonRepository;
use App\Services\ConfigService;

class LessonService implements \App\Services\LessonService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonRepository */
  private $lessonRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param LessonRepository $lessonRepository
   */
  public function __construct(ConfigService $configService, LessonRepository $lessonRepository) {
    $this->configService = $configService;
    $this->lessonRepository = $lessonRepository;
  }

  public function getForTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false, $withCourse = false) {
    $lessons = $this->lessonRepository
        ->forTeacher($teacher, $start, $end, $dayOfWeek, $numbers, $showCancelled, $withCourse)
        ->orderBy('lessons.number')
        ->with('course')
        ->get(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.room', 'lessons.cancelled', 'lessons.course_id']);
    $lessons->each(function(Lesson $lesson) {
      $this->setTimes($lesson);
    });
    return $lessons;
  }

  public function getForDay(Teacher $teacher, Date $date = null) {
    return $this->getForTeacher($teacher, $date ?: Date::today(), null, null, null, true);
  }

  public function getForCourse(Course $course) {
    $lessons = $course->lessons()
        ->orderBy('date')
        ->orderBy('number')
        ->get(['id', 'date', 'number', 'cancelled']);
    $lessons->each(function(Lesson $lesson) {
      $this->setTimes($lesson);
    });
    return $lessons;
  }

  public function getLessonCount(Date $date) {
    return $this->configService->getAsInt('lesson.count.' . $date->dayOfWeek, 0);
  }

  public function getAllLessonTimes() {
    $lessons = [];
    for ($d = 0; $d < 7; $d++) {
      $n = $this->configService->getAsInt('lesson.count.' . $d);
      if ($n > 0) {
        $lessons[$d] = [];
        for ($l = 1; $l <= $n; $l++) {
          $lessons[$d][$l] = [
              'start' => $this->configService->getAsString('lesson.start.' . $d . '.' . $l),
              'end'   => $this->configService->getAsString('lesson.end.' . $d . '.' . $l)
          ];
        }
      }
    }
    return $lessons;
  }

  public function getDaysWithoutLessons($lessons = null) {
    $daysWithoutLessons = [];
    for ($d = 0; $d < 7; $d++) {
      if ($lessons) {
        if (empty($lessons[$d])) {
          $daysWithoutLessons[] = $d;
        }
      } else if (!$this->configService->getAsInt('lesson.count.' . $d)) {
        $daysWithoutLessons[] = $d;
      }
    }
    return $daysWithoutLessons;
  }

  public function getStart(Date $date, $number) {
    return $this->configService->getAsString('lesson.start.' . $date->dayOfWeek . '.' . $number);
  }

  public function getEnd(Date $date, $number) {
    return $this->configService->getAsString('lesson.end.' . $date->dayOfWeek . '.' . $number);
  }

  public function isAttendanceChecked(Lesson $lesson) {
    return !$lesson->registrations()->whereNull('attendance')->exists();
  }

  public function setTimes(Lesson $lesson) {
    $lesson->start = $this->getStart($lesson->date, $lesson->number);
    $lesson->end = $this->getEnd($lesson->date, $lesson->number);
  }

}
