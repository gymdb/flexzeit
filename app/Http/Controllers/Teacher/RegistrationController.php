<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class RegistrationController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var MiscService */
  private $miscService;

  /** @var OffdayService */
  private $offdayService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var StudentService */
  private $studentService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param MiscService $miscService
   * @param OffdayService $offdayService
   * @param RegistrationService $registrationService
   * @param StudentService $studentService
   */
  public function __construct(ConfigService $configService, MiscService $miscService, OffdayService $offdayService,
      RegistrationService $registrationService, StudentService $studentService) {
    $this->configService = $configService;
    $this->miscService = $miscService;
    $this->offdayService = $offdayService;
    $this->registrationService = $registrationService;
    $this->studentService = $studentService;

    $this->middleware('transaction', ['only' => ['setAttendance', 'setAttendanceChecked', 'registerLesson', 'unregisterLesson', 'refreshAbsences']]);
  }

  /**
   * Show the overview page for registrations of the students
   *
   * @return View
   */
  public function showRegistrations() {
    $this->authorize('showRegistrations', Student::class);

    $user = $this->getTeacher();
    $groups = $user->admin ? $this->miscService->getGroups() : [$user->form->group];
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $this->configService->getDefaultListStartDate();
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.list', compact('groups', 'subjects', 'teachers', 'minDate', 'maxDate',
        'defaultStartDate', 'defaultEndDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Show the list of missing registrations
   *
   * @return View
   */
  public function showMissing() {
    $this->authorize('showRegistrations', Student::class);

    $user = $this->getTeacher();
    $isAdmin = $user->admin;
    $groups = $isAdmin ? $this->miscService->getGroups() : [$user->form->group];

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getFirstRegisterDate()->copy()->addDay(-1);
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.missing', compact('isAdmin', 'groups', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Show the list of absent students
   *
   * @return View
   */
  public function showAbsent() {
    $this->authorize('showRegistrations', Student::class);

    $user = $this->getTeacher();
    $groups = $user->admin ? $this->miscService->getGroups() : [$user->form->group];

    $minDate = $this->configService->getYearStart();
    $maxDate = Date::today();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.absent', compact('groups', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Set attendance for a given registration
   *
   * @param Registration $registration
   * @param boolean $attendance
   * @return JsonResponse
   */
  public function setAttendance(Registration $registration, $attendance) {
    $this->authorize('setAttendance', $registration);

    $teacher = $this->getTeacher();
    $force = $teacher->admin || ($teacher->form && $registration->student->groups()->wherePivot('group_id', $teacher->form->group_id)->exists());
    $this->registrationService->setAttendance($registration, $attendance, $force);
    return response()->json(['success' => true]);
  }

  /**
   * Set attendance for a given registration
   *
   * @param Lesson $lesson
   * @return JsonResponse
   */
  public function setAttendanceChecked(Lesson $lesson) {
    $this->authorize('setAttendanceChecked', $lesson);

    $this->registrationService->setAttendanceChecked($lesson);
    return response()->json(['success' => true]);
  }

  /**
   * Unregister a given registration
   *
   * @param Lesson $lesson
   * @param Student $student
   * @return JsonResponse
   * @internal param Registration $registration
   */
  public function registerLesson(Lesson $lesson, Student $student) {
    $this->authorize('register', $lesson);

    $this->registrationService->registerStudentForLesson($lesson, $student, true, $this->getTeacher()->admin);
    return response()->json(['success' => true]);
  }

  /**
   * Unregister a given registration
   *
   * @param Course $course
   * @param Student $student
   * @return JsonResponse
   * @internal param Registration $registration
   */
  public function registerCourse(Course $course, Student $student) {
    $this->authorize('register', $course);

    $this->registrationService->registerStudentForCourse($course, $student, true, $this->getTeacher()->admin);
    return response()->json(['success' => true]);
  }

  /**
   * Unregister a given registration
   *
   * @param Registration $registration
   * @return JsonResponse
   */
  public function unregisterLesson(Registration $registration) {
    $this->authorize('unregister', $registration);

    $this->registrationService->unregisterStudentFromLesson($registration, true);
    return response()->json(['success' => true]);
  }

  /**
   * Refresh absences data from WebUntis
   *
   * @param Date $date
   * @return JsonResponse
   */
  public function refreshAbsences(Date $date) {
    $this->studentService->loadAbsences($date);
    return response()->json(['success' => true]);
  }

  /**
   * Get registrations for a student in JSON format
   *
   * @param Student $student
   * @param Date|null $start
   * @param Date|null $end
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return JsonResponse
   */
  public function getForStudent(Student $student, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    $this->authorize('showRegistrations', $student);

    $start = $start ?: $this->configService->getDefaultListStartDate();
    $end = $end ?: $this->configService->getDefaultListEndDate();

    $registrations = $this->registrationService->getMappedForList($student, $start, $end, $teacher, $subject);
    return response()->json($registrations);
  }

  /**
   * Get all warnings when registering a student
   *
   * @param Course $course
   * @param Student $student
   * @return JsonResponse
   */
  public function getWarningsForCourse(Course $course, Student $student) {
    $warnings = $this->registrationService->getWarningsForCourse($course, $student);
    return response()->json($warnings);
  }

  /**
   * Get all warnings when registering a student
   *
   * @param Lesson $lesson
   * @param Student $student
   * @return JsonResponse
   */
  public function getWarningsForLesson(Lesson $lesson, Student $student) {
    $warnings = $this->registrationService->getWarningsForLesson($lesson, $student);
    return response()->json($warnings);
  }

  /**
   * Get students with missing registrations
   *
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getMissing(Group $group, Student $student = null, Date $start = null, Date $end = null) {
    if ($student) {
      $this->authorize('showRegistrations', $student);
    } else {
      $this->authorize('showRegistrations', $group);
    }

    $missing = $this->registrationService->getMissing($group, $student, $start, $end);
    return response()->json($missing);
  }

  /**
   * Get list of absent students
   *
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getAbsent(Group $group, Student $student = null, Date $start = null, Date $end = null) {
    if ($student) {
      $this->authorize('showRegistrations', $student);
    } else {
      $this->authorize('showRegistrations', $group);
    }

    $missing = $this->registrationService->getMappedAbsent($group, $student, $start, $end);
    return response()->json($missing);
  }

}
