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
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RegistrationRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Validators\DateValidator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RegistrationServiceImpl implements RegistrationService {

  /** @var RegistrationRepository */
  private $registrationRepository;

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var OffdayRepository */
  private $offdayRepository;

  /** @var DateValidator */
  private $dateValidator;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param RegistrationRepository $registrationRepository
   * @param LessonRepository $lessonRepository
   * @param OffdayRepository $offdayRepository
   * @param DateValidator $dateValidator
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, RegistrationRepository $registrationRepository,
      LessonRepository $lessonRepository, OffdayRepository $offdayRepository, DateValidator $dateValidator) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->registrationRepository = $registrationRepository;
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

    $course->lessons->map(function($lesson) use ($student, $force, $admin) {
      $this->doRegister($lesson, $student, $force, $admin);
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
    $this->doRegister($lesson, $student, $force, $admin);
  }

  public function validateStudentForLesson(Lesson $lesson, Student $student, $ignoreDate = false, $force = false) {
    if (!$force) {
      if ($lesson->course()->exists()) {
        return RegistrationException::HAS_COURSE;
      }
      if (!$ignoreDate && !$this->dateValidator->validateRegisterAllowed('date', $lesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($lesson->students()->count() >= $this->configService->getMaxStudents()) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($this->offdayRepository->inRange($lesson->date, null, null, $lesson->number, $student->offdays())->exists()) {
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

  private function doRegister(Lesson $lesson, Student $student, $force, $admin) {
    if ($admin) {
      $student->registrations()
          ->whereExists(function($query) use ($lesson) {
            $query->select(DB::raw(1))
                ->from('lessons')
                ->whereColumn('lessons.id', 'registrations.lesson_id')
                ->where('date', $lesson->date)
                ->where('number', $lesson->number)
                ->where('cancelled', false);
          })
          ->delete();
    }

    $registration = new Registration(['obligatory' => $force]);
    $registration->lesson()->associate($lesson);
    $registration->student()->associate($student);
    $registration->save();
  }

  public function unregisterGroupFromCourse(Course $course, Group $group) {
    // TODO
  }

  public function unregisterStudentFromCourse(Course $course, Student $student, $force = false) {
    $registrations = $course->registrations()->where('student_id', $student->id)->get(['registrations.id', 'obligatory']);
    $lesson = $course->firstLesson();

    if (!$force) {
      $obligatory = $registrations->contains(function(Registration $reg) {
        return $reg->obligatory;
      });
      if ($obligatory) {
        throw new RegistrationException(RegistrationException::OBLIGATORY);
      }

      if (!$this->dateValidator->validateRegisterAllowed('date', $lesson->date)) {
        throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
      }
    }

    if ($lesson->date < Date::today()) {
      throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
    }

    $registrations->each(function(Registration $reg) {
      $reg->delete();
    });
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

  public function setAttendance(Registration $registration, $attendance, $force = false) {
    if (!is_bool($attendance)) {
      throw new RegistrationException(RegistrationException::INVALID_ATTENDANCE);
    }
    if ((!$force && !$registration->lesson->date->isToday()) || ($force && $registration->lesson->date->isFuture())) {
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

  private function buildForStudent(Student $student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->forStudent($student, $start, $end, $number, $showCancelled, $teacher, $subject)
        ->with('lesson', 'lesson.teacher', 'lesson.course');
  }

  public function getForStudent(Student $student, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    $registrations = $this
        ->buildforStudent($student, $start ?: $this->configService->getYearStart(), $end ?: Date::today(), null, false, $teacher, $subject)
        ->where(function($q1) {
          $q1->where('attendance', true)
              ->orWhereNull('attendance');
        })
        ->get(['registrations.id', 'registrations.lesson_id', 'documentation']);
    $registrations->each(function(Registration $registration) {
      $this->lessonService->setTime($registration->lesson);
    });
    return $registrations;
  }

  public function getSlots(Student $student, Date $date = null, Date $end = null) {
    $lessons = $this->registrationRepository
        ->getSlots($student, $date ?: Date::today(), $end)
        ->with('teacher', 'course')
        ->get(['l.id', 'l.teacher_id', 'l.course_id', 'l.room', 'r.obligatory', 'r.id as registration_id', 'd.date', 'd.number']);
    $lessons->each(function($lesson) {
      $this->lessonService->setTime($lesson);
    });

    return $lessons;
  }

  public function getMappedForList(Student $student, Date $start = null, Date $end = null, $number = null, Teacher $teacher = null, Subject $subject = null) {
    $query = $this->buildForStudent($student, $start, $end, $number, true, $teacher, $subject)
        ->select(['registrations.id', 'lesson_id', 'attendance']);
    return $this->getExcused($query)
        ->get()
        ->map(function(Registration $registration) {
          $lesson = $registration->lesson;
          $this->lessonService->setTime($lesson);
          $data = [
              'attendance' => $registration->attendance,
              'excused'    => boolval($registration->excused),
              'id'         => $registration->id,
              'date'       => $lesson->date->toDateString(),
              'time'       => $lesson->time,
              'room'       => $lesson->room,
              'teacher'    => $lesson->teacher->name(),
              'cancelled'  => $lesson->cancelled
          ];
          if ($lesson->course) {
            $data['course'] = [
                'id'   => $lesson->course->id,
                'name' => $lesson->course->name,
                'room' => $lesson->course->room
            ];
          }
          return $data;
        });
  }

  public function getMappedForSlot(Student $student, Date $date, $number) {
    return $this->buildForStudent($student, $date, null, $number)
        ->get(['lesson_id'])
        ->map(function(Registration $registration) {
          $lesson = $registration->lesson;
          return [
              'lesson_id' => $lesson->id,
              'teacher'   => $lesson->teacher->name(),
              'course'    => $lesson->course ? $lesson->course->name : null
          ];
        });
  }

  public function getMissing(Group $group, Student $student = null, Date $start = null, Date $end = null) {
    return $this->registrationRepository->getMissing($group, $student,
        $start ?: $this->configService->getYearStart(), $end ?: $this->configService->getFirstRegisterDate()->addDay(-1))
        ->orderBy('date')
        ->orderBy('lastname')
        ->orderBy('firstname')
        ->orderBy('number')
        ->get(['id', 'firstname', 'lastname', 'date', 'number'])
        ->map(function(Student $student) {
          $lesson = new Lesson(['date' => $student->date, 'number' => $student->number]);
          $this->lessonService->setTime($lesson);
          return [
              'date'   => $lesson->date->toDateString(),
              'number' => $lesson->number,
              'time'   => $lesson->time,
              'id'     => $student->id,
              'name'   => $student->name()
          ];
        });
  }

  public function getAbsent(Group $group, Student $student = null, Date $start = null, Date $end = null) {
    $query = $this->registrationRepository
        ->forStudent($student ?: $group, $start ?: $this->configService->getYearStart(), $end ?: Date::today())
        ->select(['registrations.id', 'lesson_id', 'attendance', 'registrations.student_id']);
    return $this->getExcused($query)
        ->where(function($q2) {
          $q2->where(function($q3) {
            $q3->where('attendance', false)->whereNull('a.student_id');
          })->orWhere(function($q3) {
            $q3->where('attendance', true)->whereNotNull('a.student_id');
          });
        })
        ->with('lesson', 'lesson.teacher', 'student')
        ->get()
        ->map(function(Registration $registration) {
          $lesson = $registration->lesson;
          $this->lessonService->setTime($lesson);
          return [
              'id'         => $registration->id,
              'date'       => $lesson->date->toDateString(),
              'time'       => $lesson->time,
              'teacher'    => $lesson->teacher->name(),
              'name'       => $registration->student->name(),
              'attendance' => $registration->attendance,
              'excused'    => boolval($registration->excused)
          ];
        });
  }

  private function getExcused(Builder $query) {
    return $query
        ->addSelect('a.student_id as excused')
        ->leftJoin('absences as a', function($join) {
          $join->on('a.date', 'l.date')
              ->on('a.number', 'l.number')
              ->on('a.student_id', 'registrations.student_id');
        });
  }

}
