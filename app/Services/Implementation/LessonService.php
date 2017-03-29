<?php

namespace App\Services\Implementation;

use App\Exceptions\LessonException;
use App\Models\Teacher;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Services\ConfigService;
use Carbon\Carbon;

class LessonService implements \App\Services\LessonService {

  /** @var ConfigService */
  private $configService;

  /** @var LessonRepository */
  private $lessonRepository;

  /** @var GroupRepository */
  private $groupRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param LessonRepository $lessonRepository
   * @param GroupRepository $groupRepository
   */
  public function __construct(ConfigService $configService, LessonRepository $lessonRepository, GroupRepository $groupRepository) {
    $this->configService = $configService;
    $this->lessonRepository = $lessonRepository;
    $this->groupRepository = $groupRepository;
  }

  public function getLessonsForDay($dayOfWeek, $firstLesson = 1, $lastLesson = null) {
    // Check if there are any lessons on the given start date
    $lessonCount = $this->configService->getAsInt('lesson.count.' . $dayOfWeek);
    if (empty($lessonCount)) {
      throw new LessonException(LessonException::DAY_OF_WEEK);
    }

    // Check if the lesson range given is possible
    if (is_null($lastLesson)) {
      $lastLesson = $lessonCount;
    }
    if ($firstLesson <= 0 || $firstLesson > $lastLesson || $lastLesson > $lessonCount) {
      throw new LessonException(LessonException::NUMBERS);
    }

    return range($firstLesson, $lastLesson);
  }

  public function forTeacher(Teacher $teacher, Carbon $start, Carbon $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false, $withCourse = false) {
    return $this->lessonRepository->inRange($start, $end, $dayOfWeek, $numbers, $showCancelled, $withCourse, $teacher->lessons());
  }

  public function forGroups(array $groups, Carbon $start, Carbon $end = null, $dayOfWeek = null, array $numbers = null, $showCancelled = false) {
    return $this->lessonRepository->inRange($start, $end, $dayOfWeek, $numbers, $showCancelled,
        $this->groupRepository->queryById($groups)->lessons());
  }

}
