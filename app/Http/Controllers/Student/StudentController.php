<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Services\ConfigService;
use App\Services\MiscService;
use App\Services\RegistrationService;
use App\Validators\DateValidator;
use Illuminate\View\View;

class StudentController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var MiscService */
  private $miscService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var DateValidator */
  private $dateValidator;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param MiscService $miscService
   * @param RegistrationService $registrationService
   * @param DateValidator $dateValidator
   */
  public function __construct(ConfigService $configService, MiscService $miscService, RegistrationService $registrationService, DateValidator $dateValidator) {
    $this->configService = $configService;
    $this->miscService = $miscService;
    $this->registrationService = $registrationService;
    $this->dateValidator = $dateValidator;
  }

  /**
   * Show the student dashboard
   *
   * @return View
   */
  public function dashboard() {
    $student = $this->getStudent();

    $today = $this->registrationService->getSlots($student);
    $upcoming = $this->registrationService->getSlots($student, Date::today()->addDay(), $this->configService->getLastRegisterDate());
    $documentation = $this->registrationService->getForStudent($student, $this->configService->getFirstDocumentationDate(), $this->configService->getLastDocumentationDate());
    $firstRegisterDate = $this->configService->getFirstRegisterDate();

    return view('student.dashboard', compact('today', 'upcoming', 'documentation', 'firstRegisterDate'));
  }

  /**
   * @param Date $date
   * @return View
   */
  public function day(Date $date) {
    $student = $this->getStudent();

    $lessons = $this->registrationService->getSlots($student, $date);
    $subjects = $this->miscService->getSubjects();
    $teachers = $this->miscService->getTeachers();
    $firstRegisterDate = $this->configService->getFirstRegisterDate();
    $allowRegistration = $this->dateValidator->validateRegisterAllowed('date', $date);

    return view('student.day', compact('date', 'lessons', 'subjects', 'teachers', 'firstRegisterDate', 'allowRegistration'));
  }

}
