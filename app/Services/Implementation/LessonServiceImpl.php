<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\LessonRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\App;

class LessonServiceImpl implements LessonService {

  /** @var ConfigService */
  private $configService;

  /** @var RegistrationService */
  private $registrationService;

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
    $lessons->each(function(Lesson $lesson) {
      $this->setTime($lesson);
    });

    return $lessons;
  }

  public function getMappedForTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    return $this->getForTeacher($teacher, $start, $end, $dayOfWeek, $number, $showCancelled, $withCourse)->map(function(Lesson $lesson) {
      $data = [
          'id'      => $lesson->id,
          'date'    => (string)$lesson->date,
          'time'    => $lesson->time,
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
      $this->setTime($lesson);
    });
    return $lessons;
  }

  public function getAvailableLessons(Student $student, Date $date, Teacher $teacher = null, Subject $subject = null) {
    if (!$this->registrationService) {
      $this->registrationService = App::make(RegistrationService::class);
    }

    $numbers = range(1, $this->configService->getLessonCount($date));
    $this->lessonRepository
        ->forStudent($student, $date)
        ->get(['number'])
        ->each(function(Lesson $lesson) use (&$numbers) {
          if (($key = array_search($lesson->number, $numbers)) !== false) {
            unset($numbers[$key]);
          }
        });

    $lessons = $this->lessonRepository
        ->buildAvailable($student, $date, $numbers, $teacher, $subject)
        ->get()
        ->map(function(Lesson $lesson) use ($student) {
          if ($lesson->course && $this->registrationService->validateStudentForCourse($lesson->course, $student, true) !== 0) {
            return null;
          }
          if (!$lesson->course && $this->registrationService->validateStudentForLesson($lesson, $student, true) !== 0) {
            return null;
          }

          $this->setTime($lesson);

          $data = [
              'id'      => $lesson->id,
              'date'    => $lesson->date->toDateString(),
              'time'    => $lesson->time,
              'teacher' => $lesson->teacher->name()
          ];
          if ($lesson->course) {
            $associated = $lesson->course->lessons()
                ->orderBy('date')
                ->orderBy('number')
                ->get(['date'])
                ->map(function(Lesson $l) {
                  return $l->date->toDateString();
                })->unique();

            $data['course'] = [
                'id'      => $lesson->course->id,
                'name'    => $lesson->course->name,
                'lessons' => $associated
            ];
          }
          return $data;
        })
        ->reject(function($item) {
          return !$item;
        })
        ->values();

    return $lessons;
  }

  public
  function isAttendanceChecked(Lesson $lesson) {
    return !$lesson->registrations()->whereNull('attendance')->exists();
  }

  public
  function setTime(Lesson $lesson) {
    $lesson->time = [
        'start' => $this->configService->getLessonStart($lesson->date, $lesson->number),
        'end'   => $this->configService->getLessonEnd($lesson->date, $lesson->number)
    ];
  }
}
