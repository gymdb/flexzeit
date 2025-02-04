<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Models\Form;
use App\Models\Group;
use App\Models\Offday;
use App\Repositories\OffdayRepository;
use App\Services\ConfigService;
use App\Services\OffdayService;
use App\Services\WebUntisService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OffdayServiceImpl implements OffdayService {

  /** @var ConfigService */
  private $configService;

  /** @var WebUntisService */
  private $untisService;

  /** @var OffdayRepository */
  private $offdayRepository;

  /**
   * OffdayService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param WebUntisService $untisService
   * @param OffdayRepository $offdayRepository
   */
  public function __construct(ConfigService $configService, WebUntisService $untisService, OffdayRepository $offdayRepository) {
    $this->configService = $configService;
    $this->untisService = $untisService;
    $this->offdayRepository = $offdayRepository;
  }

  public function getInRange(Date $start, Date $end = null, $frequency = null) {
    return $this->offdayRepository->queryInRange(new DateConstraints($start, $end, null, $frequency))
        ->pluck('date')
        ->map(function ($date) {
            $d = new Date($date);
            return $d->toDateString();
        });
  }



  public function loadOffdays() {
    try {
      $loaded = $this->untisService->getOffdays();
    } catch (Exception $e) {
      Log::error("Could not load offdays from WebUntis. Error message: {$e->getMessage()}");
      return;
    }

    $loaded = $loaded->mapWithKeys(function ($date) {
      $d = new Date($date);
      return [$d->toDateString() => new Offday(['date' => $d])];
    });

    $existing = $this->offdayRepository->queryWithoutGroup()
        ->get(['id', 'date'])
        ->buildDictionary(['date'], 'id');

    $this->offdayRepository->deleteById($existing->dictionaryDiff($loaded));
    $this->offdayRepository->insert($loaded->dictionaryDiff($existing));
  }

  public function loadGroupOffdays() {
    // Get start and end date for Untis query
    $start = $this->configService->getYearStart();
    $end = $this->configService->getYearEnd();

    if ($start > $end) {
      return;
    }

    // Get global lesson times
    $times = $this->configService->getLessonTimes();

    // Cache timetable for each form as a dictionary
    $timetable = DB::table('timetable')->get()->buildDictionary(['form_id', 'day', 'number']);

    // Build a dictionary of non-form groups by name
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $groups = Group::doesntHave('form')->get()
        ->mapWithKeys(function(Group $group) {
          return [$group->name => $group->id];
        });

    // Load list of forms
    $forms = Form::with('group:id,name')->get(['group_id']);

    // Load and map offdays from WebUntis
    try {
      $loaded = $forms->flatMap(function($form) use ($start, $end, $times, $timetable, $groups) {
        return $this->untisService
            ->getGroupTimetable($form->group->name, $start, $end)
            ->flatMap(function($item) use ($times, $timetable, $groups, $form) {
              if ($item['flex'] ? !$item['cancelled'] : $item['cancelled']) {
                // Only consider cancelled flex lessons and non-cancelled non-flex lessons
                return [];
              }

              $date = Date::instance($item['start']);
              if (empty($times[$date->dayOfWeek])) {
                // Ignore lessons if there is no flex on the given day
                return [];
              }

              $groupId = $item['group'] ? ($groups[$item['group']] ?? null) : $form->group_id;
              $result = [];
              foreach ($times[$date->dayOfWeek] as $n => $time) {
                if (!empty($timetable[$form->group_id][$date->dayOfWeek][$n])
                    && $date->toMyDateTime($time['start']) < $item['end']
                    && $date->toMyDateTime($time['end']) > $item['start']) {
                  // Given lesson is intersecting the timeframe of this flex lesson
                  if ($groupId) {
                    $result[] = new Offday(['group_id' => $groupId, 'date' => $date, 'number' => $n]);
                  } else {
                    Log::warning("Could not find group with name {$item['group']} for lesson {$n} on {$date}.");
                  }
                }
              }
              return $result;
            });
      })->buildDictionary(['group_id', 'date', 'number'], false);
    } catch (Exception $e) {
      Log::error("Could not load group offdays from WebUntis. Error message: {$e->getMessage()}");
      return;
    }

    $existing = $this->offdayRepository
        ->queryWithGroup(new DateConstraints($start, $end))
        ->get()
        ->buildDictionary(['group_id', 'date', 'number'], 'id');

    $this->offdayRepository->deleteById($existing->dictionaryDiff($loaded));
    $this->offdayRepository->insert($loaded->dictionaryDiff($existing));
  }

}
