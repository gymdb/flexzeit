<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\DocumentationService;
use App\Services\MiscService;
use App\Services\LessonService;
use App\Services\OffdayService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DocumentationController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var OffdayService */
  private $offdayService;

  /** @var DocumentationService */
  private $documentationService;

  /** @var MiscService */
  private $miscService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param OffdayService $offdayService
   * @param DocumentationService $documentationService
   * @param MiscService $miscService
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, OffdayService $offdayService,
      DocumentationService $documentationService, MiscService $miscService) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->offdayService = $offdayService;
    $this->documentationService = $documentationService;
    $this->miscService = $miscService;

    $this->middleware('transaction', ['only' => ['setFeedback']]);
  }

  /**
   * Show the overview page for student documentation
   *
   * @return View
   */
  public function showDocumentation() {
    $groups = $this->miscService->getGroups();
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd(Date::today());
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.documentation.documentation', compact('groups', 'subjects', 'teachers', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
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
    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: Date::today();
    $constraints = new DateConstraints($start, $end);

    $documentation = $this->documentationService->getMappedDocumentation($student, $constraints, $teacher, $subject);
    return response()->json($documentation);
  }

  /**
   * Show the overview page for student documentation
   *
   * @return View
   */
  public function showMissing() {
    $teacher = $this->getTeacher();
    $groups = $this->miscService->getGroups($teacher->admin ? null : $teacher);
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getFirstDocumentationDate()->copy()->addDay(-1);
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.documentation.missing', compact('groups', 'teachers', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Get registrations with missing documentation
   *
   * @param Group $group
   * @param Student|null $student
   * @param Teacher|null $teacher
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getMissing(Group $group, Student $student = null, Teacher $teacher = null, Date $start = null, Date $end = null) {
    if ($student) {
      $this->authorize('showMissingDocumentation', $student);
    } else {
      $this->authorize('showMissingDocumentation', $group);
    }

    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: $this->configService->getFirstDocumentationDate()->copy()->addDay(-1);
    $constraints = new DateConstraints($start, $end);

    $missing = $this->documentationService->getMappedMissing($group, $student, $constraints, $teacher);
    return response()->json($missing);
  }

  /**
   * Show the overview page for feedback for a student
   *
   * @return View
   */
  public function showFeedback() {
    $this->authorize('showFeedback', Student::class);

    $user = $this->getTeacher();
    $groups = $user->admin ? $this->miscService->getGroups() : [$user->form->group];
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd(Date::today());
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.documentation.feedback', compact('groups', 'subjects', 'teachers', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Get feedback for a specific student
   *
   * @param Student $student
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getFeedbackForStudent(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null) {
    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: Date::today();
    $constraints = new DateConstraints($start, $end);
      if (strpos($student, 'id') !== false) {
          $feedback = $this->documentationService->getMappedFeedback($student, $constraints, $teacher, $subject);
      } else {
          $feedback = $this->documentationService->getMappedFeedbackForGroup($this->getTeacher(),$constraints, $teacher, $subject);
      }

    return response()->json($feedback);
  }

  /**
   * Get feedback for a specific lesson and student
   *
   * @param Registration $registration
   * @return JsonResponse
   */
  public function getFeedbackForRegistration(Registration $registration) {
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

    $this->documentationService->setFeedback($registration, $feedback);
    return response()->json(['success' => true]);
  }

}
