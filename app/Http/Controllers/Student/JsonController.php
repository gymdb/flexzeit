<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Services\Implementation\RegistrationService;
use Illuminate\Support\Facades\Auth;

class JsonController extends Controller {

  /** @var RegistrationService */
  private $registrationService;

  /**
   * Create a new controller instance.
   *
   * @param RegistrationService $registrationService
   */
  public function __construct(RegistrationService $registrationService) {
    $this->registrationService = $registrationService;
  }

  /**
   * Show the student dashboard.
   *
   * @param Course $course
   * @return \Illuminate\Http\Response
   */
  public function registerCourse(Course $course) {
    $this->registrationService->registerStudentForCourse($course, $this->getStudent());
    return response()->json(['success' => true]);
  }

  /**
   * Show the student dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function registerLesson(Lesson $lesson) {
    $this->registrationService->registerStudentForLesson($lesson, $this->getStudent());
    return response()->json(['success' => true]);
  }

}
