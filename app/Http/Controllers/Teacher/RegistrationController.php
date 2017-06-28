<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Registration;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller {

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
