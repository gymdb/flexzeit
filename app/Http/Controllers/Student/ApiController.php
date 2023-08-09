<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\DocumentationService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Services\RegistrationType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var CourseService */
  private $courseService;

  /** @var DocumentationService */
  private $documentationService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param CourseService $courseService
   * @param DocumentationService $documentationService
   * @param LessonService $lessonService
   * @param RegistrationService $registrationService
   */
  public function __construct(ConfigService $configService, CourseService $courseService,
      DocumentationService $documentationService, LessonService $lessonService,
      RegistrationService $registrationService) {
    $this->configService = $configService;
    $this->courseService = $courseService;
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
    $this->registrationService->registerStudentForCourse($course, $this->getStudent(), RegistrationType::BY_STUDENT());
    return response()->json(['success' => true]);
  }

  /**
   * @param Lesson $lesson
   * @return JsonResponse
   */
  public function registerLesson(Lesson $lesson) {
    $this->registrationService->registerStudentForLesson($lesson, $this->getStudent(), RegistrationType::BY_STUDENT());
    return response()->json(['success' => true]);
  }

  /**
   * @param Course $course
   * @return JsonResponse
   */
  public function unregisterCourse(Course $course) {
    $this->registrationService->unregisterStudentFromCourse($course, $this->getStudent(), RegistrationType::BY_STUDENT());
    return response()->json(['success' => true]);
  }

  /**
   * @param Registration $registration
   * @return JsonResponse
   * @throws AuthorizationException Thrown if the student is not allowed to unregister from this lesson
   */
  public function unregisterLesson(Registration $registration) {
    $this->authorize('unregister', $registration);

    $this->registrationService->unregisterStudentFromLesson($registration, RegistrationType::BY_STUDENT());
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
    $constraints = new DateConstraints($date->startOfDay());
    $lessons = $this->lessonService->getAvailableLessons($this->getStudent(), $constraints, $teacher, $subject, $type);
    return response()->json($lessons);
  }

  /**
   * Get documentation for a specific lesson and student
   *
   * @param Registration $registration
   * @return JsonResponse
   * @throws AuthorizationException Thrown if the student is not allowed to read the documentation for this lesson
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
   * @throws AuthorizationException Thrown if the student is not allowed to update the documentation for this lesson
   */
  public function setDocumentation(Registration $registration, $documentation = null) {
    $this->authorize('writeDocumentation', $registration);

    $this->documentationService->setDocumentation($registration, $documentation);
    return response()->json(['success' => true]);
  }

  /**
   * Get courses in JSON format
   *
   * @param Teacher|null $teacher Teacher whose lessons are shown; defaults to all teachers
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getCourses(Teacher $teacher = null, Date $start = null, Date $end = null) {
    $start = $start ?: $this->configService->getDefaultListStartDate($end);
    $end = $end ?: $this->configService->getDefaultListEndDate($start);
    $constraints = new DateConstraints($start, $end);

    $lessons = $this->courseService->getMappedForStudent($this->getStudent(), $teacher, $constraints);
    return response()->json($lessons);
  }
}
