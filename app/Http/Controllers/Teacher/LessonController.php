<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
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

  /** @var MiscService */
  private $miscService;

  /** @var OffdayService */
  private $offdayService;

  /** @var RegistrationService */
  private $registrationService;

  /**
   * Constructor for injecting services
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param MiscService $miscService
   * @param OffdayService $offdayService
   * @param RegistrationService $registrationService
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, MiscService $miscService,
      OffdayService $offdayService, RegistrationService $registrationService) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->miscService = $miscService;
    $this->offdayService = $offdayService;
    $this->registrationService = $registrationService;
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
    $teachers = $isAdmin ? $this->miscService->getTeachers() : null;

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = max($minDate, $this->getDefaultStartDate());
    $defaultEndDate = min($maxDate, $this->getDefaultEndDate());
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.lessons.index', compact('isAdmin', 'teachers', 'defaultStartDate', 'defaultEndDate', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
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
  public function getLessons(Teacher $teacher = null, Date $start = null, Date $end = null) {
    if (!$teacher) {
      $teacher = $this->getTeacher();
    }
    $this->authorize('viewLessons', $teacher);

    $lessons = $this->lessonService->getForTeacher($teacher, $start ?: $this->getDefaultStartDate(), $end ?: $this->getDefaultEndDate());
    return response()->json($lessons);
  }

  private function getDefaultStartDate() {
    return Date::today()->addWeek(-1);
  }

  private function getDefaultEndDate() {
    return Date::today()->addWeek(1);
  }

}
