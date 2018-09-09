<?php

namespace App\Validators;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Repositories\OffdayRepository;
use App\Services\ConfigService;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class CourseValidator {

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

  public function validateCreateAllowed(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return $value->between($this->configService->getFirstCourseCreateDate(), $this->configService->getLastCourseCreateDate());
  }

  public function validateEditAllowed(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return $value->copy()->addWeek(1) >= $this->configService->getFirstCourseCreateDate()
        && $value <= $this->configService->getLastCourseCreateDate();
  }

  public function validateSchoolDay(/** @noinspection PhpUnusedParameterInspection */
      $attribute, Date $value) {
    return !$this->offdayRepository->queryInRange(new DateConstraints($value))->exists();
  }

  public function validateLessonNumber(/** @noinspection PhpUnusedParameterInspection */
      $attribute, $value, $parameters, Validator $validator) {
    $date = Arr::get($validator->getData(), $parameters[0]);
    return $date && $date instanceof Date && (int)$value <= $this->configService->getLessonCount($date);
  }

  public function validateYearFrom(/** @noinspection PhpUnusedParameterInspection */
      $attribute, $value, $parameters, Validator $validator) {
    $value = (int)$value;
    $yearTo = Arr::get($validator->getData(), $parameters[0]);
    $minYear = $this->configService->getMinYear();
    $maxYear = $this->configService->getMaxYear();
    return $value >= $minYear && $value <= ($yearTo ? min($yearTo, $maxYear) : $maxYear);
  }

  public function validateYearTo(/** @noinspection PhpUnusedParameterInspection */
      $attribute, $value, $parameters, Validator $validator) {
    $value = (int)$value;
    $yearFrom = Arr::get($validator->getData(), $parameters[0]);
    $minYear = $this->configService->getMinYear();
    $maxYear = $this->configService->getMaxYear();
    return $value >= ($yearFrom ? max($yearFrom, $minYear) : $minYear) && $value <= $maxYear;
  }

}