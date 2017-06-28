<?php

namespace App\Services\Implementation;

use App\Exceptions\RegistrationException;
use App\Helpers\Date;
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
   * @param RegistrationRepository $registrationRepository
   * @param ConfigService $configService
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
    // TODO
  }

  public function getDocumentation(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null) {
    return $this->registrationRepository
        ->forStudent($student, $start ?: $this->configService->getYearStart(), $end ?: Date::today(), null, $teacher, $subject)
        ->with('lesson', 'lesson.teacher')
        ->get(['documentation', 'lesson_id'])
        ->map(function(Registration $reg) {
          return [
              'documentation' => $reg->documentation,
              'lesson'        => ['date' => (string)$reg->lesson->date],
              'teacher'       => $reg->lesson->teacher->name()
          ];
        });
  }

  public function getFeedback(Student $student, Teacher $teacher = null, Subject $subject = null, Date $start = null, Date $end = null) {
    return $this->registrationRepository
        ->forStudent($student, $start ?: $this->configService->getYearStart(), $end ?: Date::today(), null, $teacher, $subject)
        ->whereNotNull('feedback')
        ->with('lesson', 'lesson.teacher')
        ->get(['feedback', 'lesson_id'])
        ->map(function(Registration $reg) {
          return [
              'feedback' => $reg->feedback,
              'lesson'   => ['date' => (string)$reg->lesson->date],
              'teacher'  => $reg->lesson->teacher->name()
          ];
        });
  }

}
