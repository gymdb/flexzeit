<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\DocumentationService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller {

  /** @var DocumentationService */
  private $documentationService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /**
   * Create a new controller instance.
   *
   * @param DocumentationService $documentationService
   * @param LessonService $lessonService
   * @param RegistrationService $registrationService
   */
  public function __construct(DocumentationService $documentationService, LessonService $lessonService, RegistrationService $registrationService) {
    $this->documentationService = $documentationService;
    $this->lessonService = $lessonService;
    $this->registrationService = $registrationService;

    $this->middleware('transaction', ['only' => ['registerCourse', 'registerLesson', 'unregisterCourse', 'unregisterLesson', 'setDocumentation']]);
  }

  /**
   * @param Course $course
   * @return JsonResponse
   */
  public function registerCourse(Course $course) {
    $this->registrationService->registerStudentForCourse($course, $this->getStudent());
    return response()->json(['success' => true]);
  }

  /**
   * @param Lesson $lesson
   * @return JsonResponse
   */
  public function registerLesson(Lesson $lesson) {
    $this->registrationService->registerStudentForLesson($lesson, $this->getStudent());
    return response()->json(['success' => true]);
  }

  /**
   * @param Course $course
   * @return JsonResponse
   */
  public function unregisterCourse(Course $course) {
    $this->registrationService->unregisterStudentFromCourse($course, $this->getStudent());
    return response()->json(['success' => true]);
  }

  /**
   * @param Registration $registration
   * @return JsonResponse
   */
  public function unregisterLesson(Registration $registration) {
    $this->authorize('unregister', $registration);

    $this->registrationService->unregisterStudentFromLesson($registration);
    return response()->json(['success' => true]);
  }

  /**
   * @param Date $date
   * @param Teacher|null $teacher
   * @param Subject|null $subject
   * @param string|null $type
   * @return JsonResponse
   */
  public function getAvailableLessons(Date $date, Teacher $teacher = null, Subject $subject = null, $type = null) {
    $lessons = $this->lessonService->getAvailableLessons($this->getStudent(), $date, $teacher, $subject, $type);
    return response()->json($lessons);
  }

  /**
   * Get documentation for a specific lesson and student
   *
   * @param Registration $registration
   * @return JsonResponse
   */
  public function getDocumentation(Registration $registration) {
    $this->authorize('readDocumentation', $registration);

    $response = [
        'documentation' => $registration->documentation
    ];
    return response()->json($response);
  }

  /**
   * Save documentation for a specific lesson and student
   *
   * @param Registration $registration
   * @param string $documentation
   * @return JsonResponse
   */
  public function setDocumentation(Registration $registration, $documentation = null) {
    $this->authorize('writeDocumentation', $registration);

    $this->documentationService->setDocumentation($registration, $documentation);
    return response()->json(['success' => true]);
  }

}
