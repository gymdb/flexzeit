<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Models\Room;
use App\Models\RoomOccupation;
use App\Models\Teacher;
use App\Repositories\RoomRepository;
use App\Services\ConfigService;
use App\Services\RoomService;
use App\Services\WebUntisService;
use Exception;
use Illuminate\Support\Facades\Log;

class RoomServiceImpl implements RoomService {

  /** @var ConfigService */
  private $configService;

  /** @var WebUntisService */
  private $untisService;

  /** @var RoomRepository */
  private $roomRepository;

  /**
   * OffdayService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param WebUntisService $untisService
   * @param RoomRepository $roomRepository
   */
  public function __construct(ConfigService $configService, WebUntisService $untisService, RoomRepository $roomRepository) {
    $this->configService = $configService;
    $this->untisService = $untisService;
    $this->roomRepository = $roomRepository;
  }

  public function loadRoomOccupations() {
    // Get start and end date for Untis query
    $start = $this->configService->getYearStart(Date::today()->addWeek(-1));
    $end = $this->configService->getYearEnd();

    if ($start > $end) {
      return;
    }

    // Get global lesson times
    $times = $this->configService->getLessonTimes();

    // Build a dictionary of teachers
    $teachers = Teacher::get(['id', 'shortname'])->buildDictionary(['shortname'], false);

    // Load list of rooms
    $rooms = Room::whereNotNull('shortname')->get(['id', 'shortname']);

    // Load and map room occupations from WebUntis
    try {
      $loaded = $rooms->flatMap(function($room) use ($start, $end, $times, $teachers) {
        return $this->untisService
            ->getRoomOccupations($room->shortname, $start, $end)
            ->flatMap(function($item) use ($times, $teachers, $room) {
              $date = Date::instance($item['start']);
              if (empty($times[$date->dayOfWeek])) {
                // Ignore lessons if there is no flex on the given day
                return [];
              }

              $teacher = $teachers[$item['teacher']] ?? null;
              $result = [];
              foreach ($times[$date->dayOfWeek] as $n => $time) {
                if ($date->toMyDateTime($time['start']) < $item['end']
                    && $date->toMyDateTime($time['end']) > $item['start']) {
                  // Given lesson is intersecting the timeframe of this flex lesson
                  $result[] = new RoomOccupation([
                      'room_id'    => $room->id,
                      'date'       => $date,
                      'number'     => $n,
                      'teacher_id' => $teacher ? $teacher->id : null
                  ]);
                }
              }
              return $result;
            });
      })->buildDictionary(['room_id', 'date', 'number'], false);
    } catch (Exception $e) {
      Log::error("Could not load room occupations from WebUntis. Error message: {$e->getMessage()}");
      return;
    }

    $existing = $this->roomRepository
        ->queryOccupations(new DateConstraints($start, $end))
        ->get()
        ->buildDictionary(['room_id', 'date', 'number'], 'id');

    $this->roomRepository->deleteOccupationsById($existing->dictionaryDiff($loaded));
    $this->roomRepository->insertOccupations($loaded->dictionaryDiff($existing));
  }

}
