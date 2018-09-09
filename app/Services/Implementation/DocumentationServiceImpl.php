<?php

namespace App\Services\Implementation;

use App\Exceptions\RegistrationException;
use App\Helpers\DateConstraints;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Repositories\RegistrationRepository;
use App\Services\ConfigService;
use App\Services\DocumentationService;

class DocumentationServiceImpl implements DocumentationService {

  /** @var ConfigService */
  private $configService;

  /** @var RegistrationRepository */
  private $registrationRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param RegistrationRepository $registrationRepository
   */
  public function __construct(ConfigService $configService, RegistrationRepository $registrationRepository) {
    $this->configService = $configService;
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

  public function getDocumentation(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null) {
    $registrations = $this->registrationRepository
        ->queryDocumentation($student, $constraints, $teacher, $subject)
        ->with('lesson:id,date,number,teacher_id,course_id', 'lesson.teacher:id,lastname,firstname', 'lesson.course:id,name')
        ->get(['registrations.id', 'lesson_id', 'documentation']);
    $registrations->each(function(Registration $registration) {
      $this->configService->setTime($registration->lesson);
    });

    return $registrations;
  }

  public function getMappedDocumentation(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->queryDocumentation($student, $constraints, $teacher, $subject)
        ->with('lesson:id,date,teacher_id', 'lesson.teacher:id,lastname,firstname')
        ->get(['documentation', 'lesson_id'])
        ->map(function(Registration $reg) {
          return [
              'documentation' => $reg->documentation,
              'date'          => $reg->lesson->date->toDateString(),
              'teacher'       => $reg->lesson->teacher->name()
          ];
        });
  }

  public function getMappedMissing(Group $group, Student $student = null, DateConstraints $constraints, Teacher $teacher = null) {
    return $this->registrationRepository
        ->queryDocumentation($student ?: $group, $constraints, $teacher)
        ->whereNull('documentation')
        ->with('lesson:id,date,number,teacher_id', 'lesson.teacher:id,lastname,firstname', 'student:id,lastname,firstname')
        ->get(['lesson_id', 'registrations.student_id'])
        ->map(function(Registration $reg) {
          $lesson = $reg->lesson;
          $this->configService->setTime($lesson);

          return [
              'date'    => $lesson->date->toDateString(),
              'time'    => $lesson->time,
              'teacher' => $lesson->teacher->name(),
              'student' => $reg->student->name()
          ];
        });
  }

  public function getMappedFeedback(Student $student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null) {
    return $this->registrationRepository
        ->queryForStudent($student, $constraints, false, $teacher, $subject)
        ->whereNotNull('feedback')
        ->with('lesson:id,date,teacher_id', 'lesson.teacher:id,lastname,firstname')
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
