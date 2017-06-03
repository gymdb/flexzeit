<?php

namespace App\Services\Implementation;

use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\RegistrationService;
use App\Services\TeacherService;
use App\Validators\DateValidator;

class TeacherServiceImpl implements TeacherService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var RegistrationService */
  private $registrationService;

  /** @var  OffdayRepository */
  private $offdayRepository;

  /** @var TeacherRepository */
  private $teacherRepository;

  /** @var  GroupRepository */
  private $groupRepository;

  /** @var  SubjectRepository */
  private $subjectRepository;

  /** @var  DateValidator */
  private $dateValidator;

  function __construct(ConfigService $configService, LessonService $lessonService, RegistrationService $registrationService, TeacherRepository $teacherRepository,
      OffdayRepository $offdayRepository, GroupRepository $groupRepository, SubjectRepository $subjectRepository, DateValidator $dateValidator) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->registrationService = $registrationService;
    $this->teacherRepository = $teacherRepository;
    $this->offdayRepository = $offdayRepository;
    $this->groupRepository = $groupRepository;
    $this->subjectRepository = $subjectRepository;
    $this->dateValidator = $dateValidator;
  }

  public function getAll() {
    return $this->teacherRepository->query()
        ->orderBy('firstname')
        ->get(['id', 'lastname', 'firstname'])
        ->map(function(Teacher $teacher) {
          return [
              'id'   => $teacher->id,
              'name' => $teacher->name()
          ];
        });
  }

  public function getGroups() {
    return $this->groupRepository->query()
        ->orderBy('name')
        ->get(['id', 'name']);
  }

  public function getStudents(Group $group) {
    return $group->students()
        ->orderBy('lastname')
        ->orderBy('firstname')
        ->get(['id', 'lastname', 'firstname'])
        ->map(function(Student $student) {
          return [
              'id'   => $student->id,
              'name' => $student->name()
          ];
        });
  }

  public function getSubjects() {
    return $this->subjectRepository
        ->query()
        ->orderBy('name')
        ->get(['id', 'name']);
  }

}