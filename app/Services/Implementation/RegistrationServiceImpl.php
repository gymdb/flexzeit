<?php

namespace App\Services\Implementation;

use App\Exceptions\RegistrationException;
use App\Helpers\Date;
use App\Helpers\DateConstraints;
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
use App\Repositories\StudentRepository;
use App\Services\ConfigService;
use App\Services\RegistrationService;
use App\Services\RegistrationType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationServiceImpl implements RegistrationService {

  use ServiceTrait;

  /** @var RegistrationRepository */
  private $registrationRepository;

  /** @var ConfigService */
  private $configService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var OffdayRepository */
  private $offdayRepository;

  /** @var StudentRepository */
  private $studentRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param RegistrationRepository $registrationRepository
   * @param LessonRepository $lessonRepository
   * @param OffdayRepository $offdayRepository
   * @param StudentRepository $studentRepository
   */
  public function __construct(ConfigService $configService, RegistrationRepository $registrationRepository,
      LessonRepository $lessonRepository, OffdayRepository $offdayRepository, StudentRepository $studentRepository) {
    $this->configService = $configService;
    $this->registrationRepository = $registrationRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
    $this->studentRepository = $studentRepository;
  }

  public function registerStudentsForCourse(Course $course, Collection $students, RegistrationType $type, Date $firstDate = null) {
    $query = $course->lessons();
    if ($firstDate) {
      $query->where('lessons.date', '>=', $firstDate);
    }
    $lessons = $query->pluck('id');
    $this->doRegister($lessons, $students, $type);
  }

  public function registerStudentForCourse(Course $course, Student $student, RegistrationType $type) {
    if (($code = $this->validateStudentForCourse($course, $student, $type)) !== null) {
      throw new RegistrationException($code);
    }
    $this->doRegister($course->lessons->pluck('id'), collect([$student->id]), $type);
  }

  public function validateStudentForCourse(Course $course, Student $student, RegistrationType $type) {
    if ($type->ignoreValidation()) {
      return null;
    }

    $lesson = $course->firstLesson();
    if (!$type->ignoreLimitations()) {
      if ($course->groups()->exists()) {
        return RegistrationException::OBLIGATORY;
      }
      if (!$this->isRegistrationPossible($lesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($course->maxstudents && $course->students()->count('student_id') >= $course->maxstudents) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($this->offdayRepository->queryForLessonsWithStudent($course->lessons, $student)->exists()) {
        return RegistrationException::OFFDAY;
      }
      if ($this->validateYear($course, $student)) {
        return RegistrationException::YEAR;
      }
    } else if ($lesson->date->isPast()) {
      return RegistrationException::REGISTRATION_PERIOD;
    }

    if ($this->registrationRepository->queryForLessons($course->lessons->pluck('id')->all(), [$student->id])->exists()) {
      return RegistrationException::ALREADY_REGISTERED;
    }
    if (!$this->studentRepository->queryTimetable($student, $lesson->date->dayOfWeek, $lesson->number)->exists()) {
      return RegistrationException::TIMETABLE;
    }

    return null;
  }

  public function registerStudentsForLesson(Lesson $lesson, Collection $students, RegistrationType $type) {
    $this->doRegister(collect([$lesson->id]), $students, $type);
  }

  public function registerStudentForLesson(Lesson $lesson, Student $student, RegistrationType $type) {
    if (($code = $this->validateStudentForLesson($lesson, $student, $type)) !== null) {
      throw new RegistrationException($code);
    }
    $this->doRegister(collect([$lesson->id]), collect([$student->id]), $type);
  }

  public function registerGroupsForLesson(Lesson $lesson, Collection $groups) {
    if ($groups->isNotEmpty()) {
      $students = $this->registrationRepository->queryMissingForLesson($lesson, $groups)->pluck('id');
      if ($students->isNotEmpty()) {
        $this->registerStudentsForLesson($lesson, $students, RegistrationType::SUBSTITUTED());
      }
    }
  }

  public function validateStudentForLesson(Lesson $lesson, Student $student, RegistrationType $type) {
    if ($type->ignoreValidation()) {
      return null;
    }

    $constraints = new DateConstraints($lesson->date, null, $lesson->number);
    if (!$type->ignoreLimitations()) {
      if ($lesson->course()->exists()) {
        return RegistrationException::HAS_COURSE;
      }
      if (!$this->isRegistrationPossible($lesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($lesson->students()->count() >= $lesson->room->capacity) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($this->offdayRepository->queryInRange($constraints, $student->offdays())->exists()) {
        return RegistrationException::OFFDAY;
      }
      if ($this->validateYear($lesson->room, $student)) {
        return RegistrationException::YEAR;
      }
    } else if ($lesson->date->isPast()) {
      return RegistrationException::REGISTRATION_PERIOD;
    }

    if ($this->lessonRepository->queryForStudent($student, $constraints)->exists()) {
      return RegistrationException::ALREADY_REGISTERED;
    }
    if (!$this->studentRepository->queryTimetable($student, $lesson->date->dayOfWeek, $lesson->number)->exists()) {
      return RegistrationException::TIMETABLE;
    }

    return null;
  }

  private function validateYear($object, $student) {
    $result = [];
    $year = $student->forms()->take(1)->pluck('year')->first();
    if ($object->yearfrom && (!$year || $year < $object->yearfrom)) {
      $result['yearfrom'] = [
          'year'     => $year,
          'yearfrom' => $object->yearfrom
      ];
    }
    if ($object->yearto && (!$year || $year > $object->yearto)) {
      $result['yearto'] = [
          'year'   => $year,
          'yearto' => $object->yearto
      ];
    }
    return $result;
  }

  private function doRegister(Collection $lessons, Collection $students, RegistrationType $type) {
    if ($lessons->isEmpty() || $students->isEmpty()) {
      return;
    }

    if ($type->unregisterExisting()) {
      $this->registrationRepository->deleteForLessons($lessons->all(), $students->all());
    }

    $existing = $this->registrationRepository
        ->queryExisting($lessons->all(), $students->all())
        ->get(['lesson_id', 'student_id'])
        ->reduce(function(array $array, Registration $registration) {
          $array[$registration->lesson_id][$registration->student_id] = true;
          return $array;
        }, []);

    $now = Carbon::now();
    $registrations = $lessons->flatMap(function($lesson) use ($students, $type, $existing, $now) {
      return $students->map(function($student) use ($lesson, $type, $existing, $now) {
        return empty($existing[$lesson][$student]) ? [
            'lesson_id'     => $lesson,
            'student_id'    => $student,
            'obligatory'    => $type->isObligatory(),
            'byteacher'     => $type->isByTeacher(),
            'registered_at' => $now
        ] : null;
      })->filter();
    });
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    Registration::insert($registrations->all());
  }

  public function unregisterStudentFromCourse(Course $course, Student $student, RegistrationType $type) {
    $lesson = $course->firstLesson();

    if (!$type->ignoreLimitations()) {
      if ($this->isObligatoryFor($course, $student)) {
        throw new RegistrationException(RegistrationException::OBLIGATORY);
      }

      if (!$this->isRegistrationPossible($lesson->date)) {
        throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
      }
    } else if ($lesson->date->isPast()) {
      throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
    }

    $this->unregisterStudentsFromCourse($course, [$student->id]);
  }

  public function unregisterStudentFromLesson(Registration $registration, RegistrationType $type) {
    $lesson = $registration->lesson;

    if (!$type->ignoreLimitations()) {
      if ($registration->obligatory) {
        throw new RegistrationException(RegistrationException::OBLIGATORY);
      }
      if ($lesson->course()->exists()) {
        throw new RegistrationException(RegistrationException::HAS_COURSE);
      }
      if (!$this->isRegistrationPossible($lesson->date)) {
        throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
      }
    } else if ($lesson->date->isPast()) {
      throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
    }

    $registration->delete();
  }

  public function unregisterAllFromCourse(Course $course, Date $firstDate = null) {
    $this->registrationRepository->deleteForCourse($course, $firstDate);
  }

  public function unregisterStudentsFromCourse(Course $course, array $students) {
    $this->registrationRepository->deleteForCourse($course, null, $students);
  }

  public function isRegistrationPossible(Date $value) {
    //allow registration on same day until 7:25
    //return ($value >= $this->configService->getFirstRegisterDate() && $value <= $this->configService->getLastRegisterDate()) ;
    return ($value >= $this->configService->getFirstRegisterDate() && $value <= $this->configService->getLastRegisterDate()) || ($value == Date::today() && date('Hi')<725);
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
    return $this->registrationRepository->queryOrdered($lesson->registrations())
        ->with(['student.absences' => function($query) use ($lesson) {
          $query->where('date', $lesson->date)->where('number', $lesson->number);
        }])
        ->with('student:id,lastname,firstname,image', 'student.forms:forms.group_id', 'student.forms.group:id,name')
        ->get(['registrations.id', 'attendance', 'lesson_id', 'student_id']);
  }

  public function getForCourse(Course $course) {
    return $this->registrationRepository->queryOrdered($course->students())
        ->with('student:id,lastname,firstname,image', 'student.forms:forms.group_id', 'student.forms.group:id,name')
        ->get(['registrations.student_id', 'students.lastname', 'students.firstname', 'students.id']);
  }

  public function getSlots(Student $student, DateConstraints $constraints) {
    $lessons = $this->registrationRepository->querySlots($student, $constraints)
        ->with('teacher:id,lastname,firstname', 'course:id,name', 'room:id,name')
        ->get(['l.id', 'l.teacher_id', 'l.course_id', 'l.room_id', 'r.obligatory', 'r.id as registration_id', 'd.date', 'd.number']);
    $offdays = $this->offdayRepository->queryForLessonsWithStudent($lessons, $student)->get();

    $lessons->each(function(Lesson $lesson) use ($offdays, $student) {
      $this->configService->setTime($lesson);

      $lesson->unregisterPossible = !$lesson->obligatory
          && $this->isRegistrationPossible($lesson->course ? $lesson->course->firstLesson()->date : $lesson->date);
      if ($lesson->course && $lesson->unregisterPossible) {
        $lesson->unregisterPossible = !$this->isObligatoryFor($lesson->course, $student);
      }

      $lesson->isOffday = $offdays->contains($this->matcher($lesson->date, $lesson->number));
    });

    return $lessons;
  }

  public function getMappedForList(Group $group, Student $student = null, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->queryWithExcused($student ?: $group, $constraints, true, $teacher, $subject)
        ->with('lesson', 'lesson.teacher:id,lastname,firstname', 'lesson.course:id,name', 'lesson.room:id,name', 'student:id,lastname,firstname')
        ->addSelect(['registrations.id', 'registrations.student_id', 'lesson_id', 'attendance'])
        ->get()
        ->map(function(Registration $registration) {
          $lesson = $registration->lesson;
          $this->configService->setTime($lesson);
          $data = [
              'attendance' => $registration->attendance,
              'excused'    => boolval($registration->excused),
              'id'         => $registration->id,
              'date'       => $lesson->date->toDateString(),
              'time'       => $lesson->time,
              'room'       => $lesson->room->name,
              'teacher'    => $lesson->teacher->name(),
              'student'    => $registration->student->name(),
              'cancelled'  => $lesson->cancelled
          ];
          if ($lesson->course) {
            $data['course'] = [
                'id'   => $lesson->course->id,
                'name' => $lesson->course->name
            ];
          }
          return $data;
        });
  }

  public function getMissingSportsRegistration(Group $group = null, DateConstraints $constraints ) {
    return $this->registrationRepository->queryMissingSportsRegistration($group, $constraints)
      ->get(['id', 'firstname', 'lastname'])
      ->map(function(Student $student) use ($group) {
        $lesson = new Lesson(['date' => $student->date, 'number' => $student->number]);
        $this->configService->setTime($lesson);
        $name = $group ? $student->name() : $student->name() . $student->formsString();
        return [
          'name' => $name,
          'id'   => $student->id,
        ];
    });
  }

  public function getMissing(Group $group = null, Student $student = null, DateConstraints $constraints) {
    return $this->registrationRepository->queryMissing($group, $student, $constraints)
        ->get(['id', 'firstname', 'lastname', 'date', 'number'])
        ->map(function(Student $student) use ($group) {
          $lesson = new Lesson(['date' => $student->date, 'number' => $student->number]);
          $this->configService->setTime($lesson);
          $name = $group ? $student->name() : $student->name() . $student->formsString();
          return [
              'date'   => $lesson->date->toDateString(),
              'number' => $lesson->number,
              'time'   => $lesson->time,
              'id'     => $student->id,
              'name'   => $name
          ];
        });
  }

  public function getMappedAbsent(Group $group, Student $student = null, DateConstraints $constraints) {
    return $this->registrationRepository->queryAbsent($student ?: $group, $constraints)
        ->addSelect(['registrations.id', 'lesson_id', 'attendance', 'registrations.student_id'])
        ->with('lesson:id,date,number,teacher_id', 'lesson.teacher:id,lastname,firstname', 'student:id,lastname,firstname')
        ->get()
        ->map(function(Registration $registration) {
          $lesson = $registration->lesson;
          $this->configService->setTime($lesson);
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

  public function getByTeacher(Group $group = null, Student $student = null, DateConstraints $constraints) {
    return $this->registrationRepository->queryByTeacher($student ?: $group, $constraints)
        ->addSelect(['registrations.id', 'registrations.student_id', 'lesson_id', 'registered_at'])
        ->with('lesson', 'lesson.teacher:id,lastname,firstname', 'student:id,lastname,firstname')
        ->get()
        ->map(function(Registration $registration) use ($group) {
          $this->configService->setTime($registration->lesson);
          $student = $group ? $registration->student->name() : $registration->student->name() . $registration->student->formsString();
          return [
              'date'          => $registration->lesson->date->toDateString(),
              'number'        => $registration->lesson->number,
              'time'          => $registration->lesson->time,
              'teacher'       => $registration->lesson->teacher->name(),
              'student'       => $student,
              'registered_at' => $registration->registered_at ? $registration->registered_at->toDateTimeString() : null
          ];
        });
  }

  public function getWarningsForCourse(Course $course, Student $student) {
    $lesson = $course->firstLesson();
    $warnings = [];

    if ($course->groups->isNotEmpty()) {
      $warnings['obligatory'] = $course->groups->pluck('name');
    }

    $participants = $course->students()->count('student_id');
    $maxstudents = $course->maxstudents;
    if ($maxstudents && $participants >= $maxstudents) {
      $warnings['maxstudents'] = [
          'students'    => $participants,
          'maxstudents' => $maxstudents
      ];
    }

    $warnings += $this->validateYear($course, $student);

    $offdays = $this->offdayRepository->queryForLessonsWithStudent($course->lessons, $student)
        ->with('group')
        ->orderBy('date')
        ->get()
        ->map(function($offday) {
          return [
              'date'  => $offday->date->toDateString(),
              'group' => $offday->group->name
          ];
        });
    if ($offdays->isNotEmpty()) {
      $warnings['offdays'] = $offdays;
    }

    if (!$this->studentRepository->queryTimetable($student, $lesson->date->dayOfWeek, $lesson->number)->exists()) {
      $warnings['timetable'] = true;
    }

    $registeredLessons = $this->registrationRepository
        ->queryForLessons($course->lessons->pluck('id')->all(), [$student->id])
        ->with('lesson:id,date,teacher_id,course_id', 'lesson.teacher:id,lastname,firstname', 'lesson.course:id,name')
        ->get(['id', 'lesson_id'])
        ->map(function(Registration $registration) {
          $lesson = $registration->lesson;
          return [
              'id'      => $lesson->id,
              'date'    => $lesson->date->toDateString(),
              'teacher' => $lesson->teacher->name(),
              'course'  => $lesson->course ? $lesson->course->name : null
          ];
        });
    if ($registeredLessons->isNotEmpty()) {
      $warnings['lessons'] = $registeredLessons;
    }

    return $warnings;
  }

  public function getWarningsForLesson(Lesson $lesson, Student $student) {
    $warnings = [];

    if ($lesson->course) {
      $course = $lesson->course;

      if ($course->groups->isNotEmpty()) {
        $warnings['obligatory'] = $course->groups->pluck('name');
      }

      $participants = $course->students()->count('student_id');
      $maxstudents = $course->maxstudents;

      $warnings += $this->validateYear($course, $student);
    } else {
      $participants = $lesson->students()->count();
      $maxstudents = $lesson->room->capacity;

      $warnings += $this->validateYear($lesson->room, $student);
    }

    if ($maxstudents && $participants >= $maxstudents) {
      $warnings['maxstudents'] = [
          'students'    => $participants,
          'maxstudents' => $maxstudents
      ];
    }

    $constraints = new DateConstraints($lesson->date, null, $lesson->number);
    if ($this->offdayRepository->queryInRange($constraints, $student->offdays())->exists()) {
      $warnings['offday'] = true;
    }

    if (!$this->studentRepository->queryTimetable($student, $lesson->date->dayOfWeek, $lesson->number)->exists()) {
      $warnings['timetable'] = true;
    }

    $registeredLesson = $this->lessonRepository
        ->queryForStudent($student, $constraints)
        ->with('teacher', 'course')
        ->first(['lessons.id', 'lessons.teacher_id', 'lessons.course_id']);
    if ($registeredLesson) {
      if ($registeredLesson->course) {
        $warnings['course'] = [
            'id'      => $registeredLesson->id,
            'teacher' => $registeredLesson->teacher->name(),
            'course'  => $registeredLesson->course->name
        ];
      } else {
        $warnings['lesson'] = [
            'id'      => $registeredLesson->id,
            'teacher' => $registeredLesson->teacher->name()
        ];
      }
    }

    return $warnings;
  }

  private function isObligatoryFor(Course $course, Student $student) {
    return $course->registrations()->where('student_id', $student->id)->where('obligatory', true)->exists();
  }

}
