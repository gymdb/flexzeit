<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Repositories\LessonRepository;
use App\Services\ConfigService;
use App\Services\LessonService;

class LessonServiceImpl implements LessonService {

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
        ->with('course', 'teacher')
        ->get(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.room', 'lessons.cancelled', 'lessons.course_id', 'lessons.teacher_id']);
    return $lessons->map(function(Lesson $lesson) {
      $this->setTimes($lesson);
      $data = [
          'id'      => $lesson->id,
          'date'    => (string)$lesson->date,
          'start'   => $lesson->start,
          'end'     => $lesson->end,
          'room'    => $lesson->room,
          'teacher' => $lesson->teacher->name()
      ];
      if ($lesson->course) {
        $data['course'] = [
            'id'   => $lesson->course->id,
            'name' => $lesson->course->name,
            'room' => $lesson->course->room
        ];
      }
      return $data;
    });
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

  public function isAttendanceChecked(Lesson $lesson) {
    return !$lesson->registrations()->whereNull('attendance')->exists();
  }

  public function setTimes(Lesson $lesson) {
    $lesson->start = $this->configService->getLessonStart($lesson->date, $lesson->number);
    $lesson->end = $this->configService->getLessonEnd($lesson->date, $lesson->number);
  }

}
