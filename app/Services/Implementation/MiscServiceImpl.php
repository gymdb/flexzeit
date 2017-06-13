<?php

namespace App\Services\Implementation;

use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherRepository;
use App\Services\MiscService;

class MiscServiceImpl implements MiscService {

  /** @var GroupRepository */
  private $groupRepository;

  /** @var SubjectRepository */
  private $subjectRepository;

  /** @var TeacherRepository */
  private $teacherRepository;

  function __construct(GroupRepository $groupRepository, SubjectRepository $subjectRepository, TeacherRepository $teacherRepository) {
    $this->groupRepository = $groupRepository;
    $this->subjectRepository = $subjectRepository;
    $this->teacherRepository = $teacherRepository;
  }

  public function getGroups() {
    return $this->groupRepository->query()
        ->orderBy('name')
        ->get(['id', 'name']);
  }

  public function getSubjects() {
    return $this->subjectRepository
        ->query()
        ->orderBy('name')
        ->get(['id', 'name']);
  }

  public function getTeachers() {
    return $this->teacherRepository->query()
        ->orderBy('lastname')
        ->orderBy('firstname')
        ->get(['id', 'lastname', 'firstname'])->map(function(Teacher $teacher) {
          return [
              'id'   => $teacher->id,
              'name' => $teacher->name()
          ];
        });
  }

}