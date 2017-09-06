<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Group;
use App\Models\Student;
use App\Repositories\StudentRepository;
use App\Services\ConfigService;
use App\Services\StudentService;
use App\Services\WebUntisService;
use Illuminate\Support\Facades\Log;

class StudentServiceImpl implements StudentService {

  use ServiceTrait;

  /** @var ConfigService */
  private $configService;

  /** @var StudentRepository */
  private $studentRepository;

  /** @var WebUntisService */
  private $untisService;

  /**
   * @param ConfigService $configService
   * @param StudentRepository $studentRepository
   * @param WebUntisService $untisService
   */
  function __construct(ConfigService $configService, StudentRepository $studentRepository, WebUntisService $untisService) {
    $this->configService = $configService;
    $this->studentRepository = $studentRepository;
    $this->untisService = $untisService;
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

  public function loadAbsences(Date $date) {
    $absences = $this->untisService->getAbsences($date);
    $times = $this->configService->getLessonTimes();

    $this->studentRepository->deleteAbsences($date);

    $students = $this->studentRepository->queryForUntisId($absences->pluck('id'))->get(['id', 'untis_id']);
    $create = $absences->flatMap(function($absence) use ($times, $students) {
      $student = $students->first(function($item) use ($absence) {
        return $item->untis_id === $absence['id'];
      });
      if (!$student) {
        Log::warning('Could not find student for Untis ID ' . $absence['id'] . '.');
        return [];
      }
      $create = [];

      $start = $this->configService->getYearStart(Date::instance($absence['start']));
      $end = $this->configService->getYearEnd(Date::instance($absence['end']));

      for ($date = $start; $date <= $end; $date = $date->copy()->addDay(1)) {
        if (!empty($times[$date->dayOfWeek])) {
          foreach ($times[$date->dayOfWeek] as $n => $time) {
            if ($date->toDateTime($time['start']) >= $absence['start']
                && $date->toDateTime($time['end']) <= $absence['end']
                && !$student->absences->contains($this->matcher($date, $n))
            ) {
              $create[] = ['student_id' => $student->id, 'date' => $date, 'number' => $n];
            }
          }
        }
      }

      return $create;
    });

    $this->studentRepository->insertAbsences($create);
  }

}