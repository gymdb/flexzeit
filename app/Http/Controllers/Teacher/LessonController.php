<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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

    $this->middleware('transaction', ['only' => ['cancel', 'reinstate', 'substitute']]);
  }

  /**
   * Show the teacher dashboard
   *
   * @return View
   */
  public function dashboard() {
    $lessons = $this->lessonService->getForDay($this->getTeacher());
    return view('teacher.lessons.dashboard', compact('lessons'));
  }

  /**
   * Display a listing of all lessons
   *
   * @return View
   */
  public function index() {
    $isAdmin = $this->getTeacher()->admin;
    $teachers = $isAdmin ? $this->miscService->getTeachers() : null;

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $this->configService->getDefaultListStartDate();
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();
    $category = array();
    $number = array();

    return view('teacher.lessons.index', compact(
        'isAdmin', 'teachers', 'defaultStartDate', 'defaultEndDate', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek','category','number'
    ));
  }

  /**
   * Display a specific lesson
   *
   * @param Lesson $lesson
   * @return View
   */
  public function show(Lesson $lesson) {
    $this->authorize('view', $lesson);

    $registrations = $this->registrationService->getForLesson($lesson);
    $this->configService->setTime($lesson);

    $attendanceChecked = $this->lessonService->isAttendanceChecked($lesson);

    $isOwnLesson = ($lesson->teacher_id == $this->getTeacher()->id);
    $isAdmin = $this->getTeacher()->admin;

    $attendanceChangeable = $isOwnLesson && $lesson->date->isToday() && !$lesson->cancelled;
    $showAttendance = !$lesson->date->isFuture() && !$lesson->cancelled;
    $showFeedback = $isOwnLesson && !$lesson->date->isFuture() && !$lesson->cancelled;
    $showRegister = ($isAdmin || !$lesson->date->isPast()) && !$lesson->cancelled;
    $allowCancel = $isAdmin && !$lesson->date->isPast();
    $showSubstitute = $allowCancel && $this->lessonService->hasRegistrationsWithoutDuplicates($lesson);

    $groups = $showRegister ? $this->miscService->getGroups() : null;
    $teachers = $showSubstitute ? $this->miscService->getTeachers() : null;

    return view('teacher.lessons.show', compact('lesson', 'registrations', 'attendanceChecked', 'attendanceChangeable', 'showAttendance',
        'showFeedback', 'showRegister', 'groups', 'teachers', 'isAdmin', 'allowCancel', 'showSubstitute'));
  }

  /**
   * Cancel the specified lesson
   *
   * @param  Lesson $lesson
   * @return RedirectResponse
   */
  public function cancel(Lesson $lesson) {
    $this->authorize('cancel', $lesson);
    $this->lessonService->cancelLesson($lesson);
    return redirect(route('teacher.lessons.show', [$lesson->id]));
  }

  /**
   * Reinstate the specified lesson
   *
   * @param  Lesson $lesson
   * @return RedirectResponse
   */
  public function reinstate(Lesson $lesson) {
    $this->authorize('cancel', $lesson);
    $this->lessonService->reinstateLesson($lesson);
    return redirect(route('teacher.lessons.show', [$lesson->id]));
  }

  /**
   * Get lessons for a teacher in JSON format
   *
   * @param Teacher|null $teacher Teacher whose lessons are shown; defaults to currently logged in user
   * @param Date|null $start
   * @param Date|null $end
   * @param int|null $number
   * @return JsonResponse
   */
  public function getForTeacher(Teacher $teacher = null, Date $start = null, Date $end = null, int $number = null) {
    if (!$teacher) {
      $user = $this->getTeacher();
      $teacher = $user->admin ? null : $user;
    }
    $this->authorize('viewLessons', $teacher ?: Teacher::class);

    $start = $start ?: $this->configService->getDefaultListStartDate($end);
    $end = $end ?: $this->configService->getDefaultListEndDate($start);
    $constraints = new DateConstraints($start, $end, $number);
    $lessons = $this->lessonService->getMappedForTeacher($teacher, $constraints, true);
    return response()->json($lessons);
  }

  /**
   * Get information for substituting a lesson
   *
   * @param Lesson $lesson
   * @param Teacher $teacher
   * @return JsonResponse
   */
  public function getSubstituteInformation(Lesson $lesson, Teacher $teacher) {
    $this->authorize('cancel', $lesson);
    $data = $this->lessonService->getSubstituteInformation($lesson, $teacher);
    return response()->json($data);
  }

  /**
   * Save a substitution
   *
   * @param Lesson $lesson
   * @param Teacher $teacher
   * @return JsonResponse
   */
  public function substitute(Lesson $lesson, Teacher $teacher) {
    $this->authorize('cancel', $lesson);
    $this->lessonService->substituteLesson($lesson, $teacher);
    return response()->json(['success' => true]);
  }

}
