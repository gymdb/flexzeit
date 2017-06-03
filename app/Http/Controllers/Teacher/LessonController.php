<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Controller for all lesson related pages for teachers
 *
 * @package App\Http\Controllers\Teacher
 */
class LessonController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var TeacherService */
  private $teacherService;

  /**
   * Constructor for injecting services
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param RegistrationService $registrationService
   * @param TeacherService $teacherService
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, RegistrationService $registrationService,
      TeacherService $teacherService) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->registrationService = $registrationService;
    $this->teacherService = $teacherService;
  }

  /**
   * Show the teacher dashboard
   *
   * @return \Illuminate\Http\Response
   */
  public function dashboard() {
    $lessons = $this->lessonService->getForDay($this->getTeacher());
    return view('teacher.lessons.dashboard', compact('lessons'));
  }

  /**
   * Display a listing of all lessons
   *
   * @return Response
   */
  public function index() {
    $isAdmin = $this->getTeacher()->admin;
    $minDate = $this->configService->getAsDate('year.start');
    $maxDate = $this->configService->getAsDate('year.end');
    $teachers = $isAdmin ? $this->teacherService->getAll() : collect([]);

    return view('teacher.lessons.index', compact('isAdmin', 'minDate', 'maxDate', 'teachers'));
  }

  /**
   * Display a specific lesson
   *
   * @param Lesson $lesson
   * @return Response
   */
  public function show(Lesson $lesson) {
    $this->authorize('view', $lesson);

    $registrations = $this->registrationService->getForLesson($lesson);
    $this->lessonService->setTimes($lesson);

    $attendanceChecked = $this->lessonService->isAttendanceChecked($lesson);

    $attendanceChangeable = $lesson->date->isToday() && !$lesson->cancelled;
    $showAttendance = !$lesson->date->isFuture() && !$lesson->cancelled;
    $showFeedback = !$lesson->date->isFuture() && !$lesson->cancelled;
    $showUnregister = !$lesson->date->isPast() && !$lesson->cancelled;

    return view('teacher.lessons.show', compact(
        'lesson', 'registrations', 'attendanceChecked', 'attendanceChangeable', 'showAttendance', 'showFeedback', 'showUnregister'));
  }

  /**
   * Get lessons in JSON format
   *
   * @param Teacher|null $teacher Teacher whose lessons are shown; defaults to currently logged in user
   * @param Date $start
   * @param Date $end
   * @return JsonResponse
   */
  public function getLessons(Teacher $teacher = null, Date $start, Date $end) {
    if (!$teacher) {
      $teacher = $this->getTeacher();
    }
    $this->authorize('viewLessons', $teacher);

    $lessons = $this->lessonService->getForTeacher($teacher, $start, $end);
    return response()->json($lessons);
  }

}
