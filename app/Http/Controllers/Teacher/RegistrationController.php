<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
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
use App\Services\RegistrationType;
use App\Services\StudentService;
use Illuminate\Auth\Access\AuthorizationException;
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

    $this->middleware('transaction', ['only' => [
        'setAttendance', 'setAttendanceChecked', 'registerLesson', 'unregisterLesson', 'refreshAbsences'
    ]]);
  }

  /**
   * Show the overview page for registrations of the students
   *
   * @return View
   * @throws AuthorizationException
   */
  public function showRegistrations() {
    $this->authorize('showRegistrations', Student::class);

    $groups = $this->miscService->getGroups();
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();

    $user = $this->getTeacher();
    $defaultGroup = !$user->admin && $user->form ? $user->form->group_id : null;
    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $this->configService->getDefaultListStartDate();
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.list', compact(
        'groups', 'subjects', 'teachers', 'defaultGroup', 'minDate', 'maxDate',
        'defaultStartDate', 'defaultEndDate', 'offdays', 'disabledDaysOfWeek'
    ));
  }

  /**
   * Show the list of missing registrations
   *
   * @return View
   * @throws AuthorizationException
   */
  public function showMissing() {
    $this->authorize('showMissingRegistrations', Student::class);

    $user = $this->getTeacher();
    $isAdmin = $user->admin;
    $groups = $isAdmin ? $this->miscService->getGroups() : [$user->form->group];

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getFirstRegisterDate()->copy()->addDay(-1);
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.missing', compact(
        'isAdmin', 'groups', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'
    ));
  }

  /**
   * Show the list of absent students
   *
   * @return View
   * @throws AuthorizationException
   */
  public function showAbsent() {
    $this->authorize('showAbsent', Student::class);

    $user = $this->getTeacher();
    $groups = $user->admin ? $this->miscService->getGroups() : [$user->form->group];

    $minDate = $this->configService->getYearStart();
    $maxDate = Date::today();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.absent', compact(
        'groups', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'
    ));
  }

  /**
   * Show the list of missing registrations
   *
   * @return View
   * @throws AuthorizationException
   */
  public function showByTeacher() {
    $this->authorize('showByTeacherRegistrations', Student::class);

    $user = $this->getTeacher();
    $isAdmin = $user->admin;
    $groups = $isAdmin ? $this->miscService->getGroups() : [$user->form->group];

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.registrations.byteacher', compact('isAdmin', 'groups', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Set attendance for a given registration
   *
   * @param Registration $registration
   * @param boolean $attendance
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function setAttendance(Registration $registration, $attendance) {
    $this->authorize('setAttendance', $registration);

    $teacher = $this->getTeacher();
    $force = $teacher->admin || ($teacher->form && $registration->student->groups()
                ->wherePivot('group_id', $teacher->form->group_id)->exists());
    $this->registrationService->setAttendance($registration, $attendance, $force);
    return response()->json(['success' => true]);
  }

  /**
   * Set attendance for a given registration
   *
   * @param Lesson $lesson
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function setAttendanceChecked(Lesson $lesson) {
    $this->authorize('setAttendanceChecked', $lesson);

    $this->registrationService->setAttendanceChecked($lesson);
    return response()->json(['success' => true]);
  }

  /**
   * Register single student for a given lesson
   *
   * @param Lesson $lesson
   * @param Student $student
   * @return JsonResponse
   * @internal param Registration $registration
   * @throws AuthorizationException
   */
  public function registerLesson(Lesson $lesson, Student $student) {
    $this->authorize('register', $lesson);

    $this->registrationService->registerStudentForLesson($lesson, $student, RegistrationType::BY_TEACHER($this->getTeacher()->admin));
    return response()->json(['success' => true]);
  }

  /**
   * Register single student for a given course
   *
   * @param Course $course
   * @param Student $student
   * @return JsonResponse
   * @internal param Registration $registration
   * @throws AuthorizationException
   */
  public function registerCourse(Course $course, Student $student) {
    $this->authorize('register', $course);

    $this->registrationService->registerStudentForCourse($course, $student, RegistrationType::BY_TEACHER($this->getTeacher()->admin));
    return response()->json(['success' => true]);
  }

  /**
   * Unregister a given registration
   *
   * @param Registration $registration
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function unregisterLesson(Registration $registration) {
    $this->authorize('unregister', $registration);

    $this->registrationService->unregisterStudentFromLesson($registration, RegistrationType::BY_TEACHER($this->getTeacher()->admin));
    return response()->json(['success' => true]);
  }

  /**
   * Unregister a student from a whole course
   *
   * @param Course $course
   * @param Student $student
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function unregisterCourse(Course $course, Student $student) {
    $this->authorize('unregister', $course);

    $this->registrationService->unregisterStudentFromCourse($course, $student, RegistrationType::BY_TEACHER($this->getTeacher()->admin));
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
   * @param Group $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function getForStudent(Group $group, Student $student = null, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    if ($student) {
      $this->authorize('showRegistrations', $student);
    } else {
      $this->authorize('showRegistrations', $group);
    }

    $start = $start ?: $this->configService->getDefaultListStartDate($end);
    $end = $end ?: $this->configService->getDefaultListEndDate($start);
    $constraints = new DateConstraints($start, $end);

    $registrations = $this->registrationService->getMappedForList($group, $student, $constraints, $teacher, $subject);
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
   * @param Group|null $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function getMissing(Group $group = null, Student $student = null, Date $start = null, Date $end = null) {
    if ($student) {
      $this->authorize('showMissingRegistrations', $student);
    } else if ($group) {
      $this->authorize('showMissingRegistrations', $group);
    } else {
      $this->authorize('showMissingRegistrations', Group::class);
    }

    if (!$group && !$student && !$start && !$end && $this->getTeacher()->admin) {
      $start = $end = Date::today();
    } else {
      $start = $start ?: $this->configService->getYearStart();
      $end = $end ?: $this->configService->getFirstRegisterDate()->copy()->addDay(-1);
    }
    $constraints = new DateConstraints($start, $end);

    $missing = $this->registrationService->getMissing($group, $student, $constraints);
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
   * @throws AuthorizationException
   */
  public function getAbsent(Group $group, Student $student = null, Date $start = null, Date $end = null) {
    if ($student) {
      $this->authorize('showAbsent', $student);
    } else {
      $this->authorize('showAbsent', $group);
    }

    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: Date::today();
    $constraints = new DateConstraints($start, $end);

    $missing = $this->registrationService->getMappedAbsent($group, $student, $constraints);
    return response()->json($missing);
  }

  /**
   * Get students with registrations made by a teacher
   *
   * @param Group|null $group
   * @param Student|null $student
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   * @throws AuthorizationException
   */
  public function getByTeacher(Group $group = null, Student $student = null, Date $start = null, Date $end = null) {
    if ($student) {
      $this->authorize('showByTeacherRegistrations', $student);
    } else if ($group) {
      $this->authorize('showByTeacherRegistrations', $group);
    } else {
      $this->authorize('showByTeacherRegistrations', Group::class);
    }

    if (!$group && !$student && !$start && !$end && $this->getTeacher()->admin) {
      $start = $end = Date::today();
    } else {
      $start = $start ?: $this->configService->getYearStart();
      $end = $end ?: $this->configService->getYearEnd();
    }
    $constraints = new DateConstraints($start, $end);

    $byTeacher = $this->registrationService->getByTeacher($group, $student, $constraints);
    return response()->json($byTeacher);
  }
}
