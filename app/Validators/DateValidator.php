<?php

namespace App\Validators;

use App\Helpers\Date;
use App\Repositories\OffdayRepository;
use App\Services\ConfigService;

class DateValidator {

  /** @var ConfigService */
  private $configService;

  /** @var OffdayRepository */
  private $offdayRepository;

  /**
   * DateValidator constructor.
   *
   * @param ConfigService $configService
   * @param OffdayRepository $offdayRepository
   */
  public function __construct(ConfigService $configService, OffdayRepository $offdayRepository) {
    $this->configService = $configService;
    $this->offdayRepository = $offdayRepository;
  }

  public function validateInYear(/** @noinspection PhpUnusedParameterInspection */
      $attribute, $value) {
    // Check if both start and end date are within the school year
    $yearStart = $this->configService->getAsDate('year.start');
    $yearEnd = $this->configService->getAsDate('year.end');
    return $yearStart && $yearEnd && $value->between($yearStart, $yearEnd);
  }

  public function validateCreateAllowed(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return $value >= $this->getDateBound('course.create');
  }

  public function validateSchoolDay(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return !$this->offdayRepository->inRange($value)->exists();
  }

  public function validateRegisterAllowed(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return $value >= $this->getDateBound('registration.end')
        && $value < $this->getDateBound('registration.begin');
  }

  /**
   * Return the date boundary (earliest or last possible date for some action) specified by a day/week config pair
   *
   * @param $key
   * @return Date
   */
  public function getDateBound($key) {
    $day = $this->configService->getAsInt($key . '.day');
    if (is_null($day)) {
      $day = 1;
      $week = 0;
    } else {
      $week = $this->configService->getAsInt($key . '.week', 0);
    }

    $today = Date::today();
    return $week <= 0
      // Allow creation up to given days before the date
        ? $today->addDays($day)
      // Allow creation up to given weeks before the date, in relation to the start of the week for the given day of week
        : $today->setToDayOfWeek($day)->startOfWeek()->addWeeks($week);
  }

}