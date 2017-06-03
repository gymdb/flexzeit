<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\Implementation\RegistrationService;
use App\Services\LessonService;
use App\Services\OffdayService;
use App\Services\TeacherService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var OffdayService */
  private $offdayService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var TeacherService */
  private $teacherService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param OffdayService $offdayService
   * @param RegistrationService $registrationService
   * @param TeacherService $teacherService
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, OffdayService $offdayService,
      RegistrationService $registrationService, TeacherService $teacherService) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->offdayService = $offdayService;
    $this->registrationService = $registrationService;
    $this->teacherService = $teacherService;
  }

  /**
   * Show the overview page for student documentation
   *
   * @return \Illuminate\Http\Response
   */
  public function showDocumentation() {
    $teachers = $this->teacherService->getAll();
    $groups = $this->teacherService->getGroups();
    $subjects = $this->teacherService->getSubjects();

    $minDate = $this->configService->getAsDate('year.start');
    $maxDate = $this->configService->getAsDate('year.end');
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->lessonService->getDaysWithoutLessons();

    return view('teacher.documentation', compact('teachers', 'groups', 'subjects', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Get documentation created by a specific student
   *
   * @param Student $student
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getDocumentation(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null) {
    $documentation = $this->registrationService->getDocumentation($student, $teacher, $subject, $start, $end);
    return response()->json($documentation);
  }

  /**
   * Get feedback for a specific lesson and student
   *
   * @param Registration $registration
   * @return JsonResponse
   */
  public function getFeedback(Registration $registration) {
    $this->authorize('readFeedback', $registration);

    $response = [
        'student'  => $registration->student->name(),
        'feedback' => $registration->feedback
    ];
    return response()->json($response);
  }

  /**
   * Save feedback for a specific lesson and student
   *
   * @param Registration $registration
   * @param string $feedback
   * @return JsonResponse
   */
  public function setFeedback(Registration $registration, $feedback = null) {
    $this->authorize('writeFeedback', $registration);

    $this->registrationService->setFeedback($registration, $feedback);
    return response()->json(['success' => true]);
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

    $this->registrationService->setAttendance($registration, $attendance);
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
   * @param Registration $registration
   * @return JsonResponse
   */
  public function unregisterLesson(Registration $registration) {
    $this->authorize('unregister', $registration);

    $this->registrationService->unregisterStudentFromLesson($registration, true);
    return response()->json(['success' => true]);
  }

}
