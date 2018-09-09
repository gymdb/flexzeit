<?php

namespace App\Services\Implementation;

use App\Models\Room;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\RoomRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherRepository;
use App\Services\MiscService;

class MiscServiceImpl implements MiscService {

  /** @var GroupRepository */
  private $groupRepository;

  /** @var RoomRepository */
  private $roomRepository;

  /** @var SubjectRepository */
  private $subjectRepository;

  /** @var TeacherRepository */
  private $teacherRepository;

  function __construct(GroupRepository $groupRepository, RoomRepository $roomRepository, SubjectRepository $subjectRepository, TeacherRepository $teacherRepository) {
    $this->groupRepository = $groupRepository;
    $this->roomRepository = $roomRepository;
    $this->subjectRepository = $subjectRepository;
    $this->teacherRepository = $teacherRepository;
  }

  public function getGroups(Teacher $teacher = null) {
    return ($teacher ? $teacher->groups() : $this->groupRepository->query())
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
        ->get(['id', 'lastname', 'firstname'])
        ->map(function(Teacher $teacher) {
          return [
              'id'   => $teacher->id,
              'name' => $teacher->name()
          ];
        });
  }

  public function getRooms() {
    return $this->roomRepository->query()
        ->orderBy('name')
        ->get(['id', 'name', 'capacity'])
        ->map(function(Room $room) {
          return [
              'id'       => $room->id,
              'name'     => $room->name,
              'capacity' => $room->capacity
          ];
        });
  }

  public function getRoomTypes() {
    return $this->roomRepository->queryTypes()->pluck('type');
  }

}