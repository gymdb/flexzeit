<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\Teacher;
use App\Repositories\LessonRepository;
use App\Services\ConfigService;
use App\Services\LessonService;
use App\Services\SubstitutionService;
use App\Services\WebUntisService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SubstitutionServiceImpl implements SubstitutionService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonService */
  private $lessonService;

  /** @var WebUntisService */
  private $untisService;

  /** @var LessonRepository */
  private $lessonRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param LessonService $lessonService
   * @param WebUntisService $untisService
   * @param LessonRepository $lessonRepository
   */
  public function __construct(ConfigService $configService, LessonService $lessonService, WebUntisService $untisService, LessonRepository $lessonRepository) {
    $this->configService = $configService;
    $this->lessonService = $lessonService;
    $this->untisService = $untisService;
    $this->lessonRepository = $lessonRepository;
  }

  public function loadSubstitutions() {
    // Get start and end date for Untis query
    $start = $this->configService->getYearStart(Date::today());
    $end = $this->configService->getYearEnd();

    if ($start > $end) {
      return;
    }

    // Load substitution data from WebUntis
    try {
      $substitutions = $this->untisService->getSubstitutions($start, $end);
    } catch (Exception $e) {
      Log::error("Could not load substitutions from WebUntis. Error message: {$e->getMessage()}");
      return;
    }

    // Get global lesson times
    $times = $this->configService->getLessonTimes();

    // Load list of teachers by shortname
    $teachers = Teacher::get(['id', 'shortname'])->buildDictionary(['shortname'], false);
    $rooms = Room::get(['id', 'shortname'])->buildDictionary(['shortname'], false);

    // Map substitution data
    $substitutions = $substitutions->flatMap(function($substitution) use ($times, $teachers, $rooms) {
      $this->mapSubstitution($substitution, $times, $teachers, $rooms);
    });

    // Load the lessons corresponding to the substitution data
    $lessons = $this->lessonRepository->queryForSubstitutions($substitutions)
        ->get(['id', 'teacher_id', 'date', 'number', 'cancelled', 'substitute_id', 'room_id', 'untis_id'])
        ->buildDictionary(['teacher_id', 'date', 'number'], false);

    $substitutions->each(function($substitution) use ($lessons) {
      $lesson = $lessons->nestedGet([$substitution['teacher']->id, $substitution['date']->toDateString(), $substitution['number']]);
      if (!$lesson) {
        // The lesson to substitute does not exist
        Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} does not exist.");
        return;
      }

      $this->handleRoomChange($lesson, $substitution);
      $this->handleSubstitution($lesson, $substitution, $lessons);
      $this->handleCancellation($lesson, $substitution);
    });
  }

  private function mapSubstitution($substitution, array $times, Collection $teachers, Collection $rooms) {
    $date = Date::instance($substitution['start']);
    if (empty($times[$date->dayOfWeek])) {
      // Ignore lessons if there is no flex on the given day
      return [];
    }

    $teacher = $teachers[$substitution['teacher']] ?? null;
    $originalTeacher = $substitution['originalTeacher'] ? ($teachers[$substitution['originalTeacher']] ?? null) : null;
    $result = [];

    if ($substitution['rooms']->count() === 1) {
      $room = $rooms[$substitution['rooms'][0]['room']] ?? null;
      $originalRoom = $substitution['rooms'][0]['originalRoom']
          ? ($rooms[$substitution['rooms'][0]['originalRoom']] ?? null)
          : null;
    } else {
      $room = $originalRoom = null;
    }

    foreach ($times[$date->dayOfWeek] as $n => $time) {
      if ($date->toDateTime($time['start']) < $substitution['start'] || $date->toDateTime($time['end']) > $substitution['end']) {
        // Ignore if currently checked flex lesson is not within the substitution's timeframe
        continue;
      }

      if (!$teacher) {
        Log::warning("Could not find teacher with shortname {$substitution['teacher']} for lesson {$date->toDateString()}/{$n}.");
        continue;
      }

      if ($substitution['rooms']->count() === 1 && !$room) {
        Log::warning("Could not find room with shortname {$substitution['rooms'][0]['room']} for lesson {$substitution['teacher']}/{$date->toDateString()}/{$n}.");
      }

      $data = [
          'untisId'      => $substitution['untisId'] ?? null,
          'date'         => $date,
          'number'       => $n,
          'teacher'      => $teacher,
          'room'         => $room,
          'originalRoom' => $originalRoom,
      ];

      switch ($substitution['type']) {
        case 'cancel':
          $data['cancel'] = true;
          break;
        case 'subst':
          if (!$originalTeacher) {
            Log::warning("Could not find teacher with shortname {$substitution['originalTeacher']} for lesson {$date->toDateString()}/{$n}.");
            $data = null;
          } else {
            $data['subst'] = true;
            $data['teacher'] = $originalTeacher;
            $data['newTeacher'] = $teacher;
          }
          break;
        case 'rmchg':
          if (!$room) {
            Log::warning("Room missing for changing room of lesson {$substitution['teacher']}/{$date->toDateString()}/{$n}.");
            $data = null;
          }
          break;
        default:
          Log::warning("Unknown substitution type '{$substitution['type']}' for teacher {$substitution['teacher']} for lesson {$date->toDateString()}/{$n}.");
          $data = null;
          break;
      }

      if ($data) {
        $result[] = $data;
      }
    }

    return $result;
  }

  private function handleRoomChange(Lesson $lesson, array $substitution) {
    if (!empty($substitution['room']) && $lesson->room->id !== $substitution['room']->id) {
      // Change room if a new room is given
      if (!empty($substitution['originalRoom']) && $lesson->room->id !== $substitution['originalRoom']->id) {
        Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} is in room {$lesson->room->shortname}, but expected to be in {$substitution['originalRoom']->shortname}.");
      }

      $lesson->room()->associate($substitution['room']);
      $lesson->save();
    }
  }

  private function handleSubstitution(Lesson $lesson, array $substitution, Collection $lessons) {
    if (!empty($substitution['subst'])) {
      // Lesson is substituted by another teacher
      if ($lesson->substitute_id) {
        // Lesson is already marked as substituted
        if ($lesson->substitute_id !== $substitution['newTeacher']->id) {
          // Substitute teacher has changed, log a warning
          Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be substituted by teacher {$substitution['newTeacher']->shortname}, but is already substituted by #{$lesson->substitute_id}.");
        }
      } else {
        $newLesson = $lessons->nestedGet([$substitution['newTeacher']->id, $substitution['date']->toDateString(), $substitution['number']]);
        if ($newLesson && $newLesson->cancelled) {
          // The teacher supposed to substitute has a cancelled lesson at the given time, log a warning and only cancel
          Log::info("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be substituted by teacher {$substitution['newTeacher']->shortname}, whose lesson is reinstated.");
        }

        $this->lessonService->substituteLesson($lesson, $substitution['newTeacher'], $substitution['untisId'], true);
      }
    }
  }

  private function handleCancellation(Lesson $lesson, array $substitution) {
    if (!empty($substitution['cancel'])) {
      // Lesson should be cancelled
      if ($lesson->untis_id && $lesson->untis_id !== $substitution['untisId']) {
        Log::info("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be cancelled, but is already bound to another untis lesson.");
      } else if ($lesson->substitute_id) {
        Log::warning("Lesson {$substitution['date']->toDateString()}/{$substitution['number']} for teacher {$substitution['teacher']->shortname} should be cancelled, but is already substituted by #{$lesson->substitute_id}.");
      } else if (!$lesson->cancelled) {
        // Only cancel if not already cancelled
        $this->lessonService->cancelLesson($lesson);
      }
    }
  }
}
