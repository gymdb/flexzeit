<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Http\Requests\Course\CreateNormalCourseRequest;
use App\Http\Requests\Course\CreateObligatoryCourseRequest;
use App\Http\Requests\Course\EditNormalCourseRequest;
use App\Http\Requests\Course\EditObligatoryCourseRequest;
use App\Models\Course;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\LessonService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller for course related pages for teachers
 *
 * @package App\Http\Controllers\Teacher
 */
class CourseController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var CourseService */
  private $courseService;

  /** @var LessonService */
  private $lessonService;

  /** @var MiscService */
  private $miscService;

  /** @var OffdayService */
  private $offdayService;

  /** @var RegistrationService */
  private $registrationService;

  /**
   * CourseController constructor.
   *
   * @param ConfigService $configService
   * @param CourseService $courseService
   * @param LessonService $lessonService
   * @param MiscService $miscService
   * @param OffdayService $offdayService
   * @param RegistrationService $registrationService
   */
  public function __construct(ConfigService $configService, CourseService $courseService, LessonService $lessonService,
      MiscService $miscService, OffdayService $offdayService, RegistrationService $registrationService) {
    $this->configService = $configService;
    $this->courseService = $courseService;
    $this->lessonService = $lessonService;
    $this->miscService = $miscService;
    $this->offdayService = $offdayService;
    $this->registrationService = $registrationService;
  }

  /**
   * Display a listing of all courses
   *
   * @return Response
   */
  public function index() {
    $isAdmin = $this->getTeacher()->admin;
    $teachers = $isAdmin ? $this->miscService->getTeachers() : null;

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $this->configService->getDefaultListStartDate();
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.courses.index', compact('isAdmin', 'teachers', 'defaultStartDate', 'defaultEndDate', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Show the form for creating a new course
   *
   * @return View
   * @throws CourseException
   */
  public function create() {
    $this->authorize('create', Course::class);

    $minDate = $this->configService->getFirstCourseCreateDate();
    $maxDate = $this->configService->getLastCourseCreateDate();
    $lessons = $this->configService->getLessonTimes();

    if ($minDate === null || $maxDate === null || empty($lessons) || $minDate > $maxDate) {
      return view('teacher.courses.impossible');
    }

    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();
    $minYear = $this->configService->getMinYear();
    $maxYear = $this->configService->getMaxYear();

    return view('teacher.courses.create', compact('minDate', 'maxDate', 'lessons', 'disabledDaysOfWeek', 'offdays', 'minYear', 'maxYear'));
  }

  /**
   * Show the form for creating a new obligatory course
   *
   * @return View
   * @throws CourseException
   */
  public function createObligatory() {
    // TODO
    $this->authorize('create', Course::class);

    $minDate = $this->configService->getFirstCourseCreateDate();
    $maxDate = $this->configService->getLastCourseCreateDate();
    $lessons = $this->configService->getLessonTimes();

    if ($minDate === null || $maxDate === null || empty($lessons) || $minDate > $maxDate) {
      return view('teacher.courses.impossible');
    }

    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();
    $minYear = $this->configService->getMinYear();
    $maxYear = $this->configService->getMaxYear();

    return view('teacher.courses.createObligatory', compact('minDate', 'maxDate', 'lessons', 'disabledDaysOfWeek', 'offdays', 'minYear', 'maxYear'));
  }

  /**
   * Store a newly created course
   *
   * @param  CreateNormalCourseRequest $request
   * @return RedirectResponse
   */
  public function store(CreateNormalCourseRequest $request) {
    $this->authorize('create', Course::class);

    $course = $this->courseService->createCourse($request, $this->getTeacher());
    return redirect(route('teacher.courses.show', [$course->id]));
  }

  /**
   * Store a newly created obligatory course
   *
   * @param  CreateObligatoryCourseRequest $request
   * @return RedirectResponse
   */
  public function storeObligatory(CreateObligatoryCourseRequest $request) {
    $this->authorize('create', Course::class);

    $course = $this->courseService->createCourse($request, $this->getTeacher());
    return redirect(route('teacher.courses.show', [$course->id]));
  }

  /**
   * Display a specific course
   *
   * @param  Course $course
   * @return View
   */
  public function show(Course $course) {
    $this->authorize('view', $course);

    $lessons = $this->lessonService->getForCourse($course);
    $registrations = $this->registrationService->getForCourse($course);

    return view('teacher.courses.show', compact('course', 'lessons', 'registrations'));
  }

  /**
   * Show the form for editing the specified course
   *
   * @param  Course $course
   * @return View
   */
  public function edit(Course $course) {
    // TODO
    $this->authorize('update', $course);
  }

  /**
   * Update the specified normal course
   *
   * @param  EditNormalCourseRequest $request
   * @param  Course $course
   * @return RedirectResponse
   */
  public function update(EditNormalCourseRequest $request, Course $course) {
    // TODO
    $this->authorize('update', $course);
  }

  /**
   * Update the specified obligatory course
   *
   * @param  EditObligatoryCourseRequest $request
   * @param  Course $course
   * @return RedirectResponse
   */
  public function updateObligatory(EditObligatoryCourseRequest $request, Course $course) {
    // TODO
    $this->authorize('update', $course);
  }

  /**
   * Remove the specified course
   *
   * @param  Course $course
   * @return RedirectResponse
   */
  public function destroy(Course $course) {
    // TODO
    $this->authorize('delete', $course);
  }

  /**
   * Get lessons for a course that is to be created
   *
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int $number
   * @return JsonResponse
   */
  public function getLessonsForCreate(Date $firstDate, Date $lastDate = null, $number) {
    $teacher = $this->getTeacher();
    $lessonsWithCourse = $this->courseService->getLessonsWithCourse($teacher, $firstDate, $lastDate, $number);
    $lessonsForNewCourse = $this->courseService->getLessonsForCourse($teacher, $firstDate, $lastDate, $number);

    return response()->json([
        'withCourse'   => $lessonsWithCourse,
        'forNewCourse' => $lessonsForNewCourse
    ]);
  }


  /**
   * Get lessons for a teacher in JSON format
   *
   * @param Teacher|null $teacher Teacher whose lessons are shown; defaults to currently logged in user
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getForTeacher(Teacher $teacher = null, Date $start = null, Date $end = null) {
    if (!$teacher) {
      $teacher = $this->getTeacher();
    }
    $this->authorize('viewCourses', $teacher);

    $start = $start ?: $this->configService->getDefaultListStartDate();
    $end = $end ?: $this->configService->getDefaultListEndDate();

    $lessons = $this->courseService->getMappedForTeacher($teacher, $start, $end);
    return response()->json($lessons);
  }

}
