<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Services\ConfigService;
use App\Services\StudentService;

class StudentController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var StudentService */
  private $studentService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param StudentService $studentService
   */
  public function __construct(ConfigService $configService, StudentService $studentService) {
    $this->configService = $configService;
    $this->studentService = $studentService;
  }

  /**
   * Show the student dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function dashboard() {
    $student = $this->getStudent();

    $today = $this->studentService->getRegistrationsForDay($student);
    $upcoming = $this->studentService->getUpcomingRegistrations($student);
    $documentation = $this->studentService->getDocumentationRegistrations($student);
    $firstRegisterDate = $this->configService->getFirstRegisterDate();

    return view('student.dashboard', compact('today', 'upcoming', 'documentation', 'firstRegisterDate'));
  }

  public function day(Date $date) {
    $student = $this->getStudent();

    $lessons = $this->studentService->getAvailableLessons($student, $date);
    $registrations = $this->studentService->getRegistrationsForDay($student, $date);
    $allowRegistration = $this->studentService->allowRegistration($date);

    return view('student.day', compact('date', 'registrations', 'lessons', 'allowRegistration'));
  }

}
