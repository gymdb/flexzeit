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
use App\Services\RegistrationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationServiceImpl implements RegistrationService {

  /** @var RegistrationRepository */
  private $registrationRepository;

  /** @var ConfigService */
  private $configService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var OffdayRepository */
  private $offdayRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param RegistrationRepository $registrationRepository
   * @param LessonRepository $lessonRepository
   * @param OffdayRepository $offdayRepository
   */
  public function __construct(ConfigService $configService, RegistrationRepository $registrationRepository,
      LessonRepository $lessonRepository, OffdayRepository $offdayRepository) {
    $this->configService = $configService;
    $this->registrationRepository = $registrationRepository;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
  }

  public function registerStudentsForCourse(Course $course, Collection $students, Date $firstDate = null) {
    $query = $course->lessons();
    if ($firstDate) {
      $query->where('lessons.date', '>=', $firstDate);
    }
    $lessons = $query->pluck('id');
    $this->doRegister($lessons, $students, true, true);
  }

  public function registerStudentForCourse(Course $course, Student $student, $force = false, $admin = false) {
    if (!$admin && ($code = $this->validateStudentForCourse($course, $student, false, $force)) !== 0) {
      throw new RegistrationException($code);
    }
    $this->doRegister($course->lessons->pluck('id'), collect([$student->id]), $force, $admin);
  }

  public function validateStudentForCourse(Course $course, Student $student, $ignoreDate = false, $force = false) {
    $firstLesson = $course->firstLesson();

    if (!$force) {
      if ($course->groups()->exists()) {
        return RegistrationException::OBLIGATORY;
      }
      if (!$ignoreDate && !$this->isRegistrationPossible($firstLesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($course->maxstudents && $firstLesson->students()->count() >= $course->maxstudents) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($student->offdays()->whereIn('date', $course->lessons->pluck('date'))->exists()) {
        return RegistrationException::OFFDAY;
      }

      $form = $student->forms()->first(['year']);
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
    if ($this->registrationRepository->queryForLessons($course->lessons->pluck('id')->all(), [$student->id])->exists()) {
      return RegistrationException::ALREADY_REGISTERED;
    }

    return 0;
  }

  public function registerStudentForLesson(Lesson $lesson, Student $student, $force = false, $admin = false) {
    if (!$admin && ($code = $this->validateStudentForLesson($lesson, $student, false, $force)) !== 0) {
      throw new RegistrationException($code);
    }
    $this->doRegister(collect([$lesson->id]), collect([$student->id]), $force, $admin);
  }

  public function validateStudentForLesson(Lesson $lesson, Student $student, $ignoreDate = false, $force = false) {
    if (!$force) {
      if ($lesson->course()->exists()) {
        return RegistrationException::HAS_COURSE;
      }
      if (!$ignoreDate && !$this->isRegistrationPossible($lesson->date)) {
        return RegistrationException::REGISTRATION_PERIOD;
      }
      if ($lesson->students()->count() >= $this->configService->getMaxStudents()) {
        return RegistrationException::MAXSTUDENTS;
      }
      if ($this->offdayRepository->queryInRange($lesson->date, null, null, $lesson->number, $student->offdays())->exists()) {
        return RegistrationException::OFFDAY;
      }
    }

    if (!$ignoreDate && $lesson->date < Date::today()) {
      return RegistrationException::REGISTRATION_PERIOD;
    }
    if ($this->lessonRepository->queryForStudent($student, $lesson->date, null, null, $lesson->number)->exists()) {
      return RegistrationException::ALREADY_REGISTERED;
    }

    return 0;
  }

  private function doRegister(Collection $lessons, Collection $students, $obligatory, $unregister) {
    if ($unregister) {
      $this->registrationRepository->deleteForLessons($lessons->all(), $students->all());
    }

    $registrations = $lessons->flatMap(function($lesson) use ($students, $obligatory) {
      return $students->map(function($student) use ($lesson, $obligatory) {
        return ['lesson_id' => $lesson, 'student_id' => $student, 'obligatory' => $obligatory];
      });
    });
    Registration::insert($registrations->all());
  }

  public function unregisterStudentFromCourse(Course $course, Student $student, $force = false) {
    $lesson = $course->firstLesson();

    if (!$force) {
      if ($this->isObligatoryFor($course, $student)) {
        throw new RegistrationException(RegistrationException::OBLIGATORY);
      }

      if (!$this->isRegistrationPossible($lesson->date)) {
        throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
      }
    }

    if ($lesson->date < Date::today()) {
      throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
    }

    $this->unregisterStudentsFromCourse($course, [$student->id]);
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
      if (!$this->isRegistrationPossible($lesson->date)) {
        throw new RegistrationException(RegistrationException::REGISTRATION_PERIOD);
      }
    }

    if ($lesson->date < Date::today()) {
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
    return $value->between($this->configService->getFirstRegisterDate(), $this->configService->getLastRegisterDate());
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
          $query->where('date', $lesson->date);
        }])
        ->get(['registrations.id', 'registrations.attendance', 'registrations.lesson_id', 'registrations.student_id']);
  }

  public function getForCourse(Course $course) {
    return $this->registrationRepository->queryOrdered($course->registrations())
        ->groupBy('registrations.student_id', 'lessons.course_id', 'students.lastname', 'students.firstname', 'students.id')
        ->get(['registrations.student_id']);
  }

  public function getSlots(Student $student, Date $date = null, Date $end = null) {
    $lessons = $this->registrationRepository->querySlots($student, $date ?: Date::today(), $end)
        ->get(['l.id', 'l.teacher_id', 'l.course_id', 'l.room', 'r.obligatory', 'r.id as registration_id', 'd.date', 'd.number']);

    $lessons->each(function(Lesson $lesson) use ($student) {
      $this->configService->setTime($lesson);

      $lesson->unregisterPossible = !$lesson->obligatory
          && $this->isRegistrationPossible($lesson->course ? $lesson->course->firstLesson()->date : $lesson->date);
      if ($lesson->course && $lesson->unregisterPossible) {
        $lesson->unregisterPossible = $this->isObligatoryFor($lesson->course, $student);
      }
    });

    return $lessons;
  }

  public function getMappedForList(Student $student, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->queryWithExcused($student, $start, $end, null, true, $teacher, $subject)
        ->with('lesson.course')
        ->addSelect(['registrations.id', 'lesson_id', 'attendance'])
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
    return $this->registrationRepository
        ->queryForStudent($student, $date, null, $number)
        ->with('lesson.course')
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
    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: $this->configService->getFirstRegisterDate()->addDay(-1);

    return $this->registrationRepository->queryMissing($group, $student, $start, $end)
        ->get(['id', 'firstname', 'lastname', 'date', 'number'])
        ->map(function(Student $student) {
          $lesson = new Lesson(['date' => $student->date, 'number' => $student->number]);
          $this->configService->setTime($lesson);
          return [
              'date'   => $lesson->date->toDateString(),
              'number' => $lesson->number,
              'time'   => $lesson->time,
              'id'     => $student->id,
              'name'   => $student->name()
          ];
        });
  }

  public function getMappedAbsent(Group $group, Student $student = null, Date $start = null, Date $end = null) {
    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: Date::today();

    return $this->registrationRepository->queryAbsent($student ?: $group, $start, $end)
        ->addSelect(['registrations.id', 'lesson_id', 'attendance', 'registrations.student_id'])
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

  private function isObligatoryFor(Course $course, Student $student) {
    return $course->registrations()->where('student_id', $student->id)->where('obligatory', true)->exists();
  }

}
