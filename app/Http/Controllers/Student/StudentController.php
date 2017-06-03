<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller {

  /** @var StudentService */
  private $studentService;

  /**
   * Create a new controller instance.
   *
   * @param StudentService $studentService
   */
  public function __construct(StudentService $studentService) {
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
    $firstRegisterDate = $this->studentService->getFirstRegisterDate();

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
