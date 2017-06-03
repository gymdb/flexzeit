<?php

namespace App\Services\Implementation;

use App\Exceptions\RegistrationException;
use App\Helpers\Date;
use App\Models\Absence;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RegistrationRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Validators\DateValidator;
use Illuminate\Support\Facades\DB;

class RegistrationServiceImpl implements RegistrationService {

  /** @var RegistrationRepository */
  private $registrationRepository;

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var GroupRepository */
  private $groupRepository;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var OffdayRepository */
  private $offdayRepository;

  /** @var DateValidator */
  private $dateValidator;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param RegistrationRepository $registrationRepository
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param GroupRepository $groupRepository
   * @param OffdayRepository $offdayRepository
   * @param DateValidator $dateValidator
   */
  public function __construct(RegistrationRepository $registrationRepository, ConfigService $configService, LessonService $lessonService,
      GroupRepository $groupRepository, LessonRepository $lessonRepository, OffdayRepository $offdayRepository, DateValidator $dateValidator) {
    $this->registrationRepository = $registrationRepository;
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->groupRepository = $groupRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
    $this->dateValidator = $dateValidator;
  }

  public function registerGroupForCourse(Course $course, Group $group) {
    // TODO
  }

  public function registerStudentForCourse(Course $course, Student $student, $force = false, $admin = false) {
    if (!$admin && ($code = $this->validateStudentForCourse($course, $student, false, $force)) !== 0) {
      throw new RegistrationException($code);
    }

    $course->lessons->map(function($lesson) use ($student, $force) {
      $this->doRegister($lesson, $student, $force);
    });
  }

  public function validateStudentForCourse(Course $course, Student $student, $ignoreDate = false, $force = false) {
    $firstLesson = $course->firstLesson();

    if (!$force) {
      if ($course->groups()->exists()) {
        return RegistrationException::OBLIGATORY;
      }
      if (!$ignoreDate && !$this->dateValidator->validateRegisterAllowed('date', $firstLesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($course->maxstudents && $firstLesson->students()->count() >= $course->maxstudents) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($student->offdays()->whereIn("date", $course->lessons()->select(["date"])->getBaseQuery())->exists()) {
        return RegistrationException::OFFDAY;
      }

      $form = $student->forms()->first(["year"]);
      if ($course->yearfrom && (!$form || $form->year < $course->yearfrom)) {
        return RegistrationException::YEAR;
      }
      if ($course->yearto && (!$form || $form->year > $course->yearto)) {
        return RegistrationException::YEAR;
      }
    }

    if (!$ignoreDate && $firstLesson->date < Date::today()) {
      return RegistrationException::REGISTRATION_PERIOD;
    }
    if ($student->registrations()
        ->whereIn("registrations.lesson_id", $course->lessons()->select(['id'])->getBaseQuery())
        ->exists()
    ) {
      return RegistrationException::ALREADY_REGISTERED;
    }

    return 0;
  }

  public function registerStudentForLesson(Lesson $lesson, Student $student, $force = false, $admin = false) {
    if (!$admin && ($code = $this->validateStudentForLesson($lesson, $student, false, $force)) !== 0) {
      throw new RegistrationException($code);
    }
    $this->doRegister($lesson, $student, $force);
  }

  public function validateStudentForLesson(Lesson $lesson, Student $student, $ignoreDate = false, $force = false) {
    if (!$force) {
      if ($lesson->course()->exists()) {
        return RegistrationException::HAS_COURSE;
      }
      if (!$ignoreDate && !$this->dateValidator->validateRegisterAllowed('date', $lesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($lesson->students()->count() >= $this->configService->getAsInt("lesson.maxstudents")) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($this->offdayRepository->inRange($lesson->date, null, null, $student->offdays())->exists()) {
        return RegistrationException::OFFDAY;
      }
    }

    if (!$ignoreDate && $lesson->date < Date::today()) {
      return RegistrationException::REGISTRATION_PERIOD;
    }
    if ($this->lessonRepository->forStudent($student, $lesson->date, null, null, [$lesson->number])->exists()) {
      return RegistrationException::ALREADY_REGISTERED;
    }

    return 0;
  }

  private function doRegister(Lesson $lesson, Student $student, $force) {
    $registration = new Registration(['obligatory' => $force]);
    $registration->lesson()->associate($lesson);
    $registration->student()->associate($student);
    $registration->save();
  }

  public function unregisterGroupFromCourse(Course $course, Group $group) {
    // TODO
  }

  public function unregisterStudentFromCourse(Course $course, Student $student, $force = false) {
    // TODO
  }

  public function unregisterStudentFromLesson(Registration $registration, $force = false) {
    $lesson = $registration->lesson;

    if (!$force) {
      if ($registration->obligatory) {
        throw new RegistrationException(RegistrationException::OBLIGATORY);
      }
      if ($lesson->course()->exists()) {
        throw new RegistrationException(RegistrationException::HAS_COURSE);
      }
      if (!$this->dateValidator->validateRegisterAllowed('date', $lesson->date)) {
        throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
      }
    }

    if ($lesson->date < Date::today()) {
      throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
    }

    $registration->delete();
  }

  public function unregisterAllFromLesson(Lesson $lesson) {
    // TODO
  }

  public function setFeedback(Registration $registration, $feedback) {
    if (!is_null($feedback) && !is_string($feedback)) {
      throw new RegistrationException(RegistrationException::INVALID_FEEDBACK);
    }
    if ($registration->lesson->date->isFuture()) {
      throw new RegistrationException(RegistrationException::FEEDBACK_PERIOD);
    }

    $registration->feedback = $feedback;
    $registration->save();
  }

  public function setAttendance(Registration $registration, $attendance) {
    if (!is_bool($attendance)) {
      throw new RegistrationException(RegistrationException::INVALID_ATTENDANCE);
    }
    if (!$registration->lesson->date->isToday()) {
      throw new RegistrationException(RegistrationException::ATTENDANCE_PERIOD);
    }

    $registration->attendance = $attendance;
    $registration->save();
  }

  public function setAttendanceChecked(Lesson $lesson) {
    if (!$lesson->date->isToday()) {
      throw new RegistrationException(RegistrationException::ATTENDANCE_PERIOD);
    }

    $sub = Absence::query()
        ->whereColumn('absences.date', 'lessons.date')
        ->whereColumn('absences.student_id', 'registrations.student_id')
        ->toSql();

    $lesson->registrations()->getQuery()
        ->whereNull('registrations.attendance')
        ->join('lessons', 'lessons.id', 'registrations.lesson_id')
        ->update(['attendance' => DB::raw('not exists(' . $sub . ')')]);
  }

  public function documentLesson(Registration $registration) {
    // TODO
  }

  public function getForLesson(Lesson $lesson) {
    $registrations = $lesson->registrations()
        ->join('students', 'students.id', 'registrations.student_id')
        ->orderBy('students.lastname')
        ->orderBy('students.firstname')
        ->orderBy('students.id')
        ->with('student', 'student.forms', 'student.forms.group')
        ->with(['student.absences' => function($query) use ($lesson) {
          $query->where('date', $lesson->date);
        }])
        ->get(['registrations.id', 'registrations.attendance', 'registrations.lesson_id', 'registrations.student_id']);

    return $registrations;
  }

  public function getForCourse(Course $course) {
    $registrations = $course->registrations()
        ->join('students', 'students.id', 'registrations.student_id')
        ->orderBy('students.lastname')
        ->orderBy('students.firstname')
        ->orderBy('students.id')
        ->with('student', 'student.forms', 'student.forms.group')
        ->get(['registrations.id', 'registrations.lesson_id', 'registrations.student_id']);

    return $registrations;
  }

  public function getDocumentation(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null) {
    return $this->registrationRepository->forStudent($student, $start ?: Date::today()->addYear(-1), Date::today()->addYear(1))
        ->with('lesson', 'lesson.teacher')
        ->get()
        ->map(function(Registration $reg) {
          $this->lessonService->setTimes($reg->lesson);
          return [
              'documentation' => $reg->documentation,
              'lesson'        => ['date' => (string)$reg->lesson->date],
              'teacher'       => $reg->lesson->teacher->name()
          ];
        });
  }

}