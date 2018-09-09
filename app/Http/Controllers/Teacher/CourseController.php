<?php

namespace App\Http\Controllers\Teacher;

use App\Exceptions\CourseException;
use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Http\Controllers\Controller;
use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\CreateNormalCourseRequest;
use App\Http\Requests\Course\CreateObligatoryCourseRequest;
use App\Http\Requests\Course\EditNormalCourseRequest;
use App\Http\Requests\Course\EditObligatoryCourseRequest;
use App\Models\Course;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\LessonService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use Illuminate\Auth\Access\AuthorizationException;
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

    $this->middleware('transaction', ['only' => ['store', 'storeObligatory', 'update', 'updateObligatory', 'destroy']]);
  }

  /**
   * Display a listing of all courses
   *
   * @return Response
   */
  public function index() {
    $teachers = $this->miscService->getTeachers();

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $this->configService->getDefaultListStartDate();
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.courses.index', compact(
        'teachers', 'defaultStartDate', 'defaultEndDate', 'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'
    ));
  }

  /**
   * Display a listing of all obligatory courses
   *
   * @return Response
   * @throws AuthorizationException Thrown if the user does not have permission to show the list of obligatory courses
   */
  public function listObligatory() {
    $this->authorize('listObligatory', Course::class);

    $groups = $this->miscService->getGroups();
    $teachers = $this->miscService->getTeachers();
    $subjects = $this->miscService->getSubjects();

    $user = $this->getTeacher();
    $defaultGroup = !$user->admin && $user->form ? $user->form->group_id : null;
    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd();
    $defaultStartDate = $this->configService->getDefaultListStartDate();
    $defaultEndDate = $this->configService->getDefaultListEndDate();
    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    return view('teacher.courses.obligatory', compact(
        'groups', 'teachers', 'subjects', 'defaultGroup', 'defaultStartDate', 'defaultEndDate',
        'minDate', 'maxDate', 'offdays', 'disabledDaysOfWeek'));
  }

  /**
   * Show the form for creating a new course
   *
   * @return View
   * @throws AuthorizationException Thrown if the user does not have permission to create a course
   */
  public function create() {
    $this->authorize('create', Course::class);

    $minYear = $this->configService->getMinYear();
    $maxYear = $this->configService->getMaxYear();

    $oldMaxStudents = $this->parseOldNumber('maxStudents');
    $oldYearFrom = $this->parseOldNumber('yearFrom');
    $oldYearTo = $this->parseOldNumber('yearTo');

    return $this->viewCreate(compact('minYear', 'maxYear', 'oldMaxStudents', 'oldYearFrom', 'oldYearTo'));
  }

  /**
   * Show the form for creating a new obligatory course
   *
   * @return View
   * @throws AuthorizationException Thrown if the user does not have permission to create an obligatory course
   */
  public function createObligatory() {
    $this->authorize('create', Course::class);

    $groups = $this->miscService->getGroups();
    $subjects = $this->miscService->getSubjects();

    $oldSubject = $this->parseOldNumber('subject');
    $oldGroups = $this->parseOldGroups();

    return $this->viewCreate(compact('groups', 'subjects', 'oldSubject', 'oldGroups'), true, 'obligatory.');
  }

  private function viewCreate(array $data, $obligatory = false, $type = '') {
    $minDate = $this->configService->getFirstCourseCreateDate();
    $maxDate = $this->configService->getLastCourseCreateDate();
    $lessons = $this->configService->getLessonTimes();

    if ($minDate === null || $maxDate === null || empty($lessons) || $minDate > $maxDate) {
      return view('teacher.courses.impossible');
    }

    $offdays = $this->offdayService->getInRange($minDate, $maxDate);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    $teachers = $this->getTeacher()->admin ? $this->miscService->getTeachers() : null;
    $rooms = $this->miscService->getRooms();

    $oldFirstDate = $this->parseOldDate('firstDate');
    $oldLastDate = $this->parseOldDate('lastDate');
    $oldNumber = $this->parseOldNumber('lessonNumber');
    $oldRoom = $this->parseOldNumber('room');
    $oldTeacher = $this->parseOldNumber('teacher');

    return view('teacher.courses.create', array_merge($data, compact(
        'minDate', 'maxDate', 'lessons', 'disabledDaysOfWeek', 'offdays', 'rooms', 'type', 'obligatory',
        'teachers', 'oldFirstDate', 'oldLastDate', 'oldNumber', 'oldRoom', 'oldTeacher'
    )));
  }

  private function parseOldDate($key) {
    $date = old($key);
    if ($date instanceof Date) {
      return $date->toDateString();
    }
    return $date ?: null;
  }

  private function parseOldNumber($key) {
    $value = old($key);
    return $value && ctype_digit($value) ? (int)$value : null;
  }

  private function parseOldGroups() {
    $oldGroups = collect(old('groups'))->map(function($group) {
      return $group && ctype_digit($group) ? (int)$group : null;
    })->filter(function($group) {
      return $group;
    });
    return $oldGroups->isEmpty() ? null : $oldGroups;
  }

  /**
   * Store a newly created course
   *
   * @param  CreateNormalCourseRequest $request
   * @return RedirectResponse
   * @throws CourseException
   * @throws AuthorizationException Thrown if the user does not have permission to create a course
   */
  public function store(CreateNormalCourseRequest $request) {
    $this->authorize('create', Course::class);

    $course = $this->courseService->createCourse($request, $this->getTeacherForCreate($request));
    return redirect(route('teacher.courses.show', [$course->id]));
  }

  /**
   * Store a newly created obligatory course
   *
   * @param  CreateObligatoryCourseRequest $request
   * @return RedirectResponse
   * @throws CourseException
   * @throws AuthorizationException Thrown if the user does not have permission to create an obligatory course
   */
  public function storeObligatory(CreateObligatoryCourseRequest $request) {
    $this->authorize('create', Course::class);

    $course = $this->courseService->createCourse($request, $this->getTeacherForCreate($request));
    return redirect(route('teacher.courses.show', [$course->id]));
  }

  private function getTeacherForCreate(CreateCourseRequest $request) {
    $user = $this->getTeacher();
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    if ($user->admin && ($id = $request->getTeacher()) && ($teacher = Teacher::find($id))) {
      return $teacher;
    }
    return $user;
  }

  /**
   * Display a specific course
   *
   * @param  Course $course
   * @return View
   */
  public function show(Course $course) {
    $lessons = $this->lessonService->getForCourse($course);
    $registrations = $this->registrationService->getForCourse($course);
    $firstLesson = $lessons->first();

    $teacher = $this->getTeacher();
    $isAdmin = $teacher->admin;
    $isOwner = $isAdmin || $teacher->id === $firstLesson->teacher_id;

    $allowDestroy = $isOwner && $firstLesson && $firstLesson->date->isFuture();
    $showRegister = $isOwner && ($isAdmin || !$firstLesson->date->isPast());

    $groups = $showRegister ? $this->miscService->getGroups() : null;

    return view('teacher.courses.show', compact(
        'course', 'lessons', 'registrations', 'firstLesson', 'allowDestroy',
        'showRegister', 'groups', 'isOwner', 'isAdmin'
    ));
  }

  /**
   * Show the form for editing the specified course
   *
   * @param  Course $course
   * @return View
   * @throws AuthorizationException Thrown if the user does not have permission to edit the given course
   */
  public function edit(Course $course) {
    $this->authorize('update', $course);

    $minDate = $this->configService->getFirstCourseCreateDate();
    $maxDate = $this->configService->getLastCourseCreateDate();

    $firstLesson = $course->firstLesson();
    $lastLesson = $course->lastLesson();

    $firstDate = $firstLesson->date;
    $lastDate = $lastLesson->date;
    $allowDateChange = ($lastDate->copy()->addWeek() >= $minDate);

    $lessons = $this->configService->getLessonTimes()[$firstDate->dayOfWeek] ?? [];
    // TODO Use actual frequency
    $offdays = $this->offdayService->getInRange($minDate, $maxDate, 1);
    $disabledDaysOfWeek = $this->configService->getDaysWithoutLessons();

    $rooms = $this->miscService->getRooms();

    $courseData = [
        'id'          => $course->id,
        'firstDate'   => $firstDate->toDateString(),
        'lastDate'    => $lastDate->toDateString(),
        'number'      => $firstLesson->number,
        'name'        => $course->name,
        'room'        => $firstLesson->room_id,
        'description' => $course->description
    ];

    $old = [
        'lastDate'    => $this->parseOldDate('lastDate') ?: $courseData['lastDate'],
        'name'        => old('name') ?: $courseData['name'],
        'room'        => $this->parseOldNumber('room') ?: $courseData['room'],
        'description' => old('description') ?: $courseData['description']
    ];

    $oldGroups = $course->groups()->pluck('id');
    $obligatory = $oldGroups->isNotEmpty();
    $type = $obligatory ? 'obligatory.' : '';

    if ($obligatory) {
      $groups = $this->miscService->getGroups();
      $subjects = $this->miscService->getSubjects();

      $courseData['subject'] = $course->subject_id;
      $courseData['groups'] = $oldGroups;

      $old['subject'] = $this->parseOldNumber('subject') ?: $courseData['subject'];
      $old['groups'] = $this->parseOldGroups() ?: $courseData['groups'];

      $allowGroupsChange = $firstDate >= $minDate;

      $groupNames = $allowGroupsChange ? null : $course->groups()->orderBy('name')->pluck('name')->implode(', ');

      $data = compact('groups', 'subjects', 'allowGroupsChange', 'groupNames');
    } else {
      $minYear = $this->configService->getMinYear();
      $maxYear = $this->configService->getMaxYear();

      $courseData['yearFrom'] = $course->yearfrom;
      $courseData['yearTo'] = $course->yearto;
      $courseData['maxStudents'] = $course->maxstudents;

      $old['yearFrom'] = $this->parseOldNumber('yearFrom') ?: $courseData['yearFrom'];
      $old['yearTo'] = $this->parseOldNumber('yearTo') ?: $courseData['yearTo'];
      $old['maxStudents'] = $this->parseOldNumber('maxStudents') ?: $courseData['maxStudents'];

      $data = compact('minYear', 'maxYear');
    }

    return view('teacher.courses.edit', array_merge($data, compact(
        'type', 'obligatory', 'allowDateChange', 'minDate', 'maxDate',
        'lessons', 'disabledDaysOfWeek', 'offdays', 'rooms', 'courseData', 'old'
    )));
  }

  /**
   * Update the specified normal course
   *
   * @param  EditNormalCourseRequest $request
   * @param  Course $course
   * @return RedirectResponse
   * @throws CourseException
   * @throws AuthorizationException Thrown if the user does not have permission to edit the given course
   */
  public function update(EditNormalCourseRequest $request, Course $course) {
    $this->authorize('update', $course);

    $course = $this->courseService->editCourse($request, $course);
    return redirect(route('teacher.courses.show', [$course->id]));
  }

  /**
   * Update the specified obligatory course
   *
   * @param  EditObligatoryCourseRequest $request
   * @param  Course $course
   * @return RedirectResponse
   * @throws CourseException
   * @throws AuthorizationException Thrown if the user does not have permission to edit the given course
   */
  public function updateObligatory(EditObligatoryCourseRequest $request, Course $course) {
    $this->authorize('update', $course);

    $course = $this->courseService->editCourse($request, $course);
    return redirect(route('teacher.courses.show', [$course->id]));
  }

  /**
   * Remove the specified course
   *
   * @param  Course $course
   * @return RedirectResponse
   * @throws AuthorizationException Thrown if the user does not have permission to delete the given course
   */
  public function destroy(Course $course) {
    $this->authorize('delete', $course);
    $this->courseService->removeCourse($course);
    return redirect(route('teacher.courses.index'));
  }

  /**
   * Get lessons for a course that is to be created
   *
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int $number
   * @param array|null $groups
   * @param Teacher|null $teacher
   * @return JsonResponse
   */
  public function getDataForCreate(Date $firstDate, Date $lastDate = null, $number, array $groups = null, Teacher $teacher = null) {
    $user = $this->getTeacher();
    if (!$user->admin || !$teacher) {
      $teacher = $user;
    }

    // TODO Get frequency from input
    $constraints = new DateConstraints($firstDate, $lastDate, $number, 1);

    $data = $this->courseService->getDataForCreate($teacher, $constraints, $groups);
    return response()->json($data);
  }

  /**
   * Get lessons for a course that is to be created
   *
   * @param Course $course
   * @param Date|null $lastDate
   * @param array|null $groups
   * @return JsonResponse
   */
  public function getDataForEdit(Course $course, Date $lastDate = null, array $groups = null) {
    $data = $this->courseService->getDataForEdit($course, $lastDate, $groups);
    return response()->json($data);
  }

  /**
   * Get courses for a teacher in JSON format
   *
   * @param Teacher|null $teacher Teacher whose lessons are shown; defaults to all teachers
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   */
  public function getForTeacher(Teacher $teacher = null, Date $start = null, Date $end = null) {
    $start = $start ?: $this->configService->getDefaultListStartDate($end);
    $end = $end ?: $this->configService->getDefaultListEndDate($start);
    $constraints = new DateConstraints($start, $end);

    $lessons = $this->courseService->getMappedForTeacher($teacher, $constraints);
    return response()->json($lessons);
  }

  /**
   * Get obligatory courses for a teacher in JSON format
   *
   * @param Group|null $group
   * @param Teacher|null $teacher Teacher whose lessons are shown; defaults to all teachers
   * @param Subject|null $subject
   * @param Date|null $start
   * @param Date|null $end
   * @return JsonResponse
   * @throws AuthorizationException Thrown if the user does not have permission to list obligatory courses
   */
  public function getObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null) {
    $this->authorize('listObligatory', Course::class);

    $start = $start ?: $this->configService->getDefaultListStartDate($end);
    $end = $end ?: $this->configService->getDefaultListEndDate($start);
    $constraints = new DateConstraints($start, $end);

    $lessons = $this->courseService->getMappedObligatory($group, $teacher, $subject, $constraints);
    return response()->json($lessons);
  }

}
