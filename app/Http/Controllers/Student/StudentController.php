<?php

namespace App\Http\Controllers\Student;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Services\ConfigService;
use App\Services\DocumentationService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use Illuminate\View\View;

class StudentController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var DocumentationService */
  private $documentationService;

  /** @var MiscService */
  private $miscService;

  /** @var OffdayService */
  private $offdayService;

  /** @var RegistrationService */
  private $registrationService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param DocumentationService $documentationService
   * @param MiscService $miscService
   * @param OffdayService $offdayService
   * @param RegistrationService $registrationService
   */
  public function __construct(ConfigService $configService, DocumentationService $documentationService,
      MiscService $miscService, OffdayService $offdayService, RegistrationService $registrationService) {
    $this->configService = $configService;
    $this->documentationService = $documentationService;
    $this->miscService = $miscService;
    $this->offdayService = $offdayService;
    $this->registrationService = $registrationService;
  }

  /**
   * Show the student dashboard
   *
   * @return View
   */
  public function dashboard() {
    $student = $this->getStudent();

    $today = $this->registrationService->getSlots($student);
    $upcoming = $this->registrationService->getSlots($student, Date::tomorrow(), $this->configService->getLastRegisterDate());
    $documentation = $this->documentationService->getDocumentation($student, $this->configService->getFirstDocumentationDate(), $this->configService->getLastDocumentationDate());

    return view('student.dashboard', compact('today', 'upcoming', 'documentation'));
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
    $roomTypes = $this->miscService->getRoomTypes();
    $allowRegistration = $this->registrationService->isRegistrationPossible($date);

    return view('student.day', compact('date', 'lessons', 'subjects', 'teachers', 'roomTypes', 'allowRegistration'));
  }

  /**
   * Display a listing of courses
   *
   * @return View
   */
  public function courses() {
    $teachers = $this->miscService->getTeachers();

    $minDate = Date::tomorrow();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $minDate;
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('student.courses', compact('teachers', 'defaultStartDate', 'defaultEndDate', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }
}
