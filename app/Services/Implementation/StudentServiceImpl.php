<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Absence;
use App\Models\Student;
use App\Repositories\Eloquent\RepositoryHelper;
use App\Services\ConfigService;
use App\Services\StudentService;
use App\Services\WebUntisService;
use Illuminate\Support\Facades\Log;

class StudentServiceImpl implements StudentService {

  /** @var ConfigService */
  private $configService;

  /** @var WebUntisService */
  private $untisService;

  function __construct(ConfigService $configService, WebUntisService $untisService) {
    $this->configService = $configService;
    $this->untisService = $untisService;
  }

  public function loadAbsences(Date $date) {
    $absences = $this->untisService->getAbsences($date);
    $times = $this->configService->getLessonTimes();
    $students = Student::whereIn('untis_id', $absences->pluck('id'))
        ->with('absences')
        ->get(['id', 'untis_id']);

    Absence::where('date', $date)->delete();

    $create = $absences->flatMap(function($absence) use ($times, $students) {
      $student = $students->first(function($item) use ($absence) {
        return $item->untis_id === $absence['id'];
      });
      if (!$student) {
        Log::warning('Could not find student for Untis ID ' . $absence['id'] . '.');
        return collect([]);
      }
      $create = [];

      $start = $this->configService->getYearStart(Date::instance($absence['start']));
      $end = $this->configService->getYearEnd(Date::instance($absence['end']));

      for ($date = $start; $date <= $end; $date = $date->copy()->addDay(1)) {
        if (!empty($times[$date->dayOfWeek])) {
          foreach ($times[$date->dayOfWeek] as $n => $time) {
            if ($date->toDateTime($time['start']) >= $absence['start']
                && $date->toDateTime($time['end']) <= $absence['end']
                && !$student->absences->contains(RepositoryHelper::matcher($date, $n))
            ) {
              $create[] = ['student_id' => $student->id, 'date' => $date, 'number' => $n];
            }
          }
        }
      }

      return collect($create);
    });

    Absence::insert($create->toArray());
  }

}