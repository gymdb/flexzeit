<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Offday;
use App\Repositories\OffdayRepository;
use App\Services\OffdayService;
use App\Services\WebUntisService;

class OffdayServiceImpl implements OffdayService {

  /** @var WebUntisService */
  private $untisService;

  /** @var OffdayRepository */
  private $offdayRepository;

  /**
   * OffdayService constructor for injecting dependencies.
   *
   * @param WebUntisService $untisService
   * @param OffdayRepository $offdayRepository
   */
  public function __construct(WebUntisService $untisService, OffdayRepository $offdayRepository) {
    $this->untisService = $untisService;
    $this->offdayRepository = $offdayRepository;
  }

  public function getInRange(Date $start, Date $end = null, $dayOfWeek = null) {
    return $this->offdayRepository->queryInRange($start, $end, $dayOfWeek)
        ->pluck('date')
        ->map(function(Date $date) {
          return $date->toDateString();
        });
  }

  public function loadOffdays() {
    $dates = $this->untisService->getOffdays();
    $this->offdayRepository->deleteWithoutGroup();
    $dates->each(function($date) {
      Offday::create(['date' => $date]);
    });
  }

}
