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
    $yearStart = $this->configService->getYearStart();
    $yearEnd = $this->configService->getYearEnd();
    return $yearStart && $yearEnd && $value->between($yearStart, $yearEnd);
  }

  public function validateCreateAllowed(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return $value >= $this->configService->getFirstCourseCreateDate();
  }

  public function validateSchoolDay(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return !$this->offdayRepository->inRange($value)->exists();
  }

  public function validateRegisterAllowed(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return $value->between($this->configService->getFirstRegisterDate(), $this->configService->getLastRegisterDate());
  }

}