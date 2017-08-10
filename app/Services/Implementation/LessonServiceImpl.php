<?php

namespace App\Services\Implementation;

use App\Exceptions\LessonException;
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
   * @param RegistrationService $registrationService
   * @param LessonRepository $lessonRepository
   */
  public function __construct(ConfigService $configService, RegistrationService $registrationService, LessonRepository $lessonRepository) {
    $this->configService = $configService;
    $this->registrationService = $registrationService;
    $this->lessonRepository = $lessonRepository;
  }

  public function cancelLesson(Lesson $lesson) {
    if ($lesson->date->isPast()) {
      throw new LessonException(LessonException::CANCEL_PERIOD);
    }

    $lesson->cancelled = true;
    $lesson->save();
  }

  public function getForTeacher(Teacher $teacher = null, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    $lessons = $this->lessonRepository
        ->queryForTeacher($teacher, $start, $end, $dayOfWeek, $number, $showCancelled, $withCourse)
        ->with('course', 'room', 'teacher')
        ->get(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.room_id', 'lessons.cancelled', 'lessons.course_id', 'lessons.teacher_id']);
    $lessons->each(function(Lesson $lesson) {
      $this->configService->setTime($lesson);
    });

    return $lessons;
  }

  public function getMappedForTeacher(Teacher $teacher = null, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    return $this->getForTeacher($teacher, $start, $end, $dayOfWeek, $number, $showCancelled, $withCourse)->map(function(Lesson $lesson) {
      $data = [
          'id'        => $lesson->id,
          'date'      => $lesson->date->toDateString(),
          'time'      => $lesson->time,
          'room'      => $lesson->room->name,
          'cancelled' => $lesson->cancelled,
          'teacher'   => $lesson->teacher->name()
      ];
      if ($lesson->course) {
        $data['course'] = [
            'id'   => $lesson->course->id,
            'name' => $lesson->course->name
        ];
        $data['maxstudents'] = $lesson->course->maxstudents;
        $data['students'] = $lesson->course->students()->count('student_id');
      } else {
        $data['maxstudents'] = $lesson->room->capacity;
        $data['students'] = $lesson->students()->count();
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
        ->get(['id', 'date', 'number', 'cancelled', 'room_id', 'teacher_id']);
    $lessons->each(function(Lesson $lesson) {
      $this->configService->setTime($lesson);
    });
    return $lessons;
  }

  public function getAvailableLessons(Student $student, Date $date, Teacher $teacher = null, Subject $subject = null, $type = null) {
    $numbers = collect(range(1, $this->configService->getLessonCount($date)))
        ->diff($this->lessonRepository->queryForStudent($student, $date)->get(['number'])->pluck('number'))
        ->all();

    $lessons = $this->lessonRepository
        ->queryAvailable($student, $date, $numbers, $teacher, $subject, $type)
        ->get(['lessons.id', 'lessons.date', 'lessons.number', 'lessons.room_id', 'lessons.teacher_id', 'lessons.course_id'])
        ->map(function(Lesson $lesson) use ($student) {
          $error = $lesson->course
              ? $this->registrationService->validateStudentForCourse($lesson->course, $student, true)
              : $this->registrationService->validateStudentForLesson($lesson, $student, true);
          if ($error) {
            return null;
          }

          $this->configService->setTime($lesson);

          $data = [
              'id'      => $lesson->id,
              'date'    => $lesson->date->toDateString(),
              'time'    => $lesson->time,
              'room'    => $lesson->room->name,
              'teacher' => [
                  'name'     => $lesson->teacher->name(),
                  'image'    => $lesson->teacher->image,
                  'info'     => $lesson->teacher->info,
                  'subjects' => $lesson->teacher->subjects->implode('name', ', ')
              ]
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
                'id'          => $lesson->course->id,
                'name'        => $lesson->course->name,
                'description' => $lesson->course->description,
                'lessons'     => $associated
            ];
          }
          return $data;
        })
        ->filter(function($item) {
          return $item;
        })
        ->values();

    return $lessons;
  }

  public function isAttendanceChecked(Lesson $lesson) {
    return !$lesson->registrations()->whereNull('attendance')->exists();
  }

}
