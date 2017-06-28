<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
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
  }

  /**
   * Show the overview page for student documentation
   *
   * @return \Illuminate\Http\Response
   */
  public function showDocumentation() {
    $groups = $this->miscService->getGroups();
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd(Date::today());
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.documentation', compact('groups', 'subjects', 'teachers', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
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
    $documentation = $this->documentationService->getDocumentation($student, $teacher, $subject, $start, $end);
    return response()->json($documentation);
  }

  /**
   * Show the overview page for feedback for a student
   *
   * @return \Illuminate\Http\Response
   */
  public function showFeedback() {
    $this->authorize('showFeedback');

    $user = $this->getTeacher();
    $groups = $user->admin ? $this->miscService->getGroups() : [$user->form->group];
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd(Date::today());
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.feedback', compact('groups', 'subjects', 'teachers', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
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
    $this->authorize('showFeedback', $student);

    $feedback = $this->documentationService->getFeedback($student, $teacher, $subject, $start, $end);
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
