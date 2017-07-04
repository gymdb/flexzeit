<?php

namespace App\Services\Implementation;

use App\Exceptions\RegistrationException;
use App\Helpers\Date;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\RegistrationRepository;
use App\Services\ConfigService;
use App\Services\DocumentationService;
use App\Services\LessonService;

class DocumentationServiceImpl implements DocumentationService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationRepository */
  private $registrationRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param RegistrationRepository $registrationRepository
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, RegistrationRepository $registrationRepository) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->registrationRepository = $registrationRepository;
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

  public function setDocumentation(Registration $registration, $documentation) {
    if (!is_null($documentation) && !is_string($documentation)) {
      throw new RegistrationException(RegistrationException::INVALID_DOCUMENTATION);
    }
    if (!$registration->lesson->date->between($this->configService->getFirstDocumentationDate(), $this->configService->getLastDocumentationDate())) {
      throw new RegistrationException(RegistrationException::DOCUMENTATION_PERIOD);
    }

    $registration->documentation = $documentation;
    $registration->save();
  }

  public function getMappedDocumentation(Student $student, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->forStudent($student, $start ?: $this->configService->getYearStart(), $end ?: Date::today(), null, false, $teacher, $subject)
        ->with('lesson', 'lesson.teacher')
        ->get(['documentation', 'lesson_id'])
        ->map(function(Registration $reg) {
          return [
              'documentation' => $reg->documentation,
              'date'          => $reg->lesson->date->toDateString(),
              'teacher'       => $reg->lesson->teacher->name()
          ];
        });
  }

  public function getMissing(Group $group, Student $student = null, Date $start = null, Date $end = null, Teacher $teacher = null) {
    $query = $this->registrationRepository
        ->forStudent($student ?: $group, $start ?: $this->configService->getYearStart(),
            $end ?: $this->configService->getFirstDocumentationDate()->addDay(-1), null, false, $teacher)
        ->where(function($q1) {
          $q1->where('attendance', true)->orWhereNull('attendance');
        })
        ->whereNull('documentation');
    if ($student) {
      $query->join('students', 'students.id', 'registrations.student_id')
          ->orderBy('students.lastname')
          ->orderBy('students.firstname');
    }
    return $query->with('lesson', 'lesson.teacher', 'student')
        ->get(['registrations.lesson_id', 'registrations.student_id'])
        ->map(function(Registration $reg) {
          $lesson = $reg->lesson;
          $this->lessonService->setTime($lesson);

          return [
              'date'    => $lesson->date->toDateString(),
              'time'    => $lesson->time,
              'teacher' => $lesson->teacher->name(),
              'student' => $reg->student->name()
          ];
        });
  }

  public function getMappedFeedback(Student $student, Date $start = null, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->forStudent($student, $start ?: $this->configService->getYearStart(), $end ?: Date::today(), null, false, $teacher, $subject)
        ->whereNotNull('feedback')
        ->with('lesson', 'lesson.teacher')
        ->get(['feedback', 'lesson_id'])
        ->map(function(Registration $reg) {
          return [
              'feedback' => $reg->feedback,
              'date'     => $reg->lesson->date->toDateString(),
              'teacher'  => $reg->lesson->teacher->name()
          ];
        });
  }

}
