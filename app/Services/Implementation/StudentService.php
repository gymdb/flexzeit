<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Helpers\DateRange;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RegistrationRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Validators\DateValidator;
use ArrayIterator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentService implements \App\Services\StudentService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var  OffdayRepository */
  private $offdayRepository;

  /** @var RegistrationRepository */
  private $registrationRepository;

  /** @var  DateValidator */
  private $dateValidator;

  function __construct(ConfigService $configService, LessonService $lessonService, RegistrationService $registrationService,
      LessonRepository $lessonRepository, OffdayRepository $offdayRepository, RegistrationRepository $registrationRepository, DateValidator $dateValidator) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->registrationService = $registrationService;
    $this->lessonRepository = $lessonRepository;
    $this->offdayRepository = $offdayRepository;
    $this->registrationRepository = $registrationRepository;
    $this->dateValidator = $dateValidator;
  }

  public function getRegistrationsForDay(Student $student, Date $date = null) {
    $date = $date ?: Date::today();
    $registrations = $this->registrationRepository
        ->forStudent($student, $date)
        ->orderBy('lessons.number')
        ->with('lesson', 'lesson.course', 'lesson.teacher')
        ->get(['registrations.id', 'registrations.lesson_id'])->getIterator();
    return $this->combineRegistrations($registrations, [$date]);
  }

  public function getUpcomingRegistrations(Student $student) {
    $start = Date::today()->addDay();
    $end = $this->dateValidator->getDateBound('registration.begin')->addDay(-1);
    $registrations = $this->registrationRepository
        ->forStudent($student, $start, $end)
        ->orderBy('lessons.number')
        ->with('lesson', 'lesson.course', 'lesson.teacher')
        ->get(['registrations.id', 'registrations.obligatory', 'registrations.lesson_id'])->getIterator();
    return $this->combineRegistrations($registrations, (new DateRange($start, $end))->toArray());
  }

  public function getDocumentationRegistrations(Student $student) {
    $end = Date::today()->addDay(-1);
    $start = $this->dateValidator->getDateBound('documentation')->addDay(-1);
    $registrations = $this->registrationRepository
        ->forStudent($student, $start, $end)
        ->orderBy('lessons.number')
        ->with('lesson', 'lesson.course', 'lesson.teacher')
        ->get(['registrations.id', 'registrations.documentation', 'registrations.lesson_id'])->getIterator();
    return $this->combineRegistrations($registrations, (new DateRange($start, $end))->toArray());
  }

  public function getAvailableLessons(Student $student, Date $date) {
    $numbers = range(1, $this->lessonService->getLessonCount($date));
    $this->lessonRepository
        ->forStudent($student, $date)
        ->get(['number'])
        ->each(function(Lesson $lesson) use (&$numbers) {
          if (($key = array_search($lesson->number, $numbers)) !== false) {
            unset($numbers[$key]);
          }
        });

    $lessons = $this->lessonRepository
        ->inRange($date, null, null, $numbers)
        ->whereExists(function($query) {
          $query->select(DB::raw(1))
              ->from('course_group')
              ->whereColumn('course_group.course_id', 'lessons.course_id');
        }, 'and', true)
        ->whereExists(function($query) {
          $query->select(DB::raw(1))
              ->from('lessons AS sub')
              ->whereColumn('sub.course_id', 'lessons.course_id')
              ->whereColumn('sub.date', '<', 'lessons.date');
        }, 'and', true)
        ->orderBy('lessons.number')
        ->with('course', 'teacher')
        ->get();

    return $this->combineLessons($lessons, $student);
  }

  public function allowRegistration(Date $date) {
    return $this->dateValidator->validateRegisterAllowed('date', $date);
  }

  public function getFirstRegisterDate() {
    return $this->dateValidator->getDateBound('registration.end');
  }

  private function combineRegistrations(ArrayIterator $items, array $dates, $addMissing = true) {
    $slots = [];
    $lesson = null;
    $registration = null;

    foreach ($dates as $date) {
      $lessonCount = $this->lessonService->getLessonCount($date);
      $first = null;

      for ($n = 1; $n <= $lessonCount; $n++) {
        $slot = null;

        while ($items->valid() && (!$lesson || $lesson->date < $date || ($lesson->date == $date && $lesson->number < $n))) {
          $item = $items->current();

          if ($item instanceof Registration) {
            $registration = $item;
            $lesson = $registration->lesson;
          } else if ($item instanceof Lesson) {
            $registration = null;
            $lesson = $item;
          }

          $items->next();
        }

        if ($lesson && $lesson->date == $date && $lesson->number === $n) {
          $firstLesson = $first ? $first['lesson'] : null;
          if (!$firstLesson || $firstLesson->teacher_id !== $lesson->teacher_id || $firstLesson->course_id != $lesson->course_id) {
            // Not a continuation of the previous slot
            $slot = ['lesson' => $lesson, 'registration' => $registration];
          }
          $registration = $lesson = null;
        } else if ($addMissing && (!$first || $first['lesson'])) {
          $slot = ['lesson' => null];
        }

        if ($slot) {
          if ($first) {
            $first['end'] = $this->lessonService->getEnd($date, $n - 1);
          }
          unset($first);

          $slot['date'] = $date;
          $slot['start'] = $this->lessonService->getStart($date, $n);
          $first =& $slot;
          $slots[] =& $slot;
        }

        unset($slot);
      }

      if ($first) {
        $first['end'] = $this->lessonService->getEnd($date, $lessonCount);
      }
      unset($first);
    }

    return $slots;
  }

  private function combineLessons(Collection $lessons, Student $student) {
    $slots = [];
    $first = null;

    foreach ($lessons as $lesson) {
      $start = $this->lessonService->getStart($lesson->date, $lesson->number);
      $end = $this->lessonService->getEnd($lesson->date, $lesson->number);

      $firstLesson = $first ? $first['lesson'] : null;
      if (!$firstLesson
          || $firstLesson->teacher_id !== $lesson->teacher_id
          || $firstLesson->course_id !== $lesson->course_id
          || $firstLesson->date != $lesson->date
          || $firstLesson->number != $lesson->number - 1
      ) {
        // Not a continuation of the previous slot
        unset($first);
        $first = null;

        if ($lesson->course) {
          if ($this->registrationService->validateStudentForCourse($lesson->course, $student, true) !== 0) {
            continue;
          }

          $associatedLessons = $lesson->course->lessons()
              ->orderBy('date')
              ->orderBy('number')
              ->get(['date'])
              ->map(function(Lesson $l) {
                return (string)$l->date;
              })->unique();
        } else {
          $associatedLessons = [[
              'id'    => $lesson->id,
              'date'  => (string)$lesson->date,
              'start' => $start,
              'end'   => $end
          ]];
        }

        $first = [
            'start'   => $start,
            'end'     => $end,
            'lesson'  => $lesson,
            'lessons' => $associatedLessons
        ];
        $slots[] =& $first;
      } else {
        $first['end'] = $end;
        if (!$firstLesson->course) {
          $first['lessons'][] = [
              'id'    => $lesson->id,
              'date'  => (string)$lesson->date,
              'start' => $start,
              'end'   => $end
          ];
        }
      }
    }

    unset($first);
    return $slots;
  }
}