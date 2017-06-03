<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Models\Offday;
use App\Repositories\OffdayRepository;
use App\Services\OffdayService;

class OffdayServiceImpl implements OffdayService {

  /** @var OffdayRepository */
  private $offdayRepository;

  /**
   * OffdayService constructor for injecting dependencies.
   *
   * @param OffdayRepository $offdayRepository
   */
  public function __construct(OffdayRepository $offdayRepository) {
    $this->offdayRepository = $offdayRepository;
  }

  public function getInRange(Date $start, Date $end = null, $dayOfWeek = null) {
    return $this->offdayRepository->inRange($start, $end, $dayOfWeek)
        ->get(['date'])
        ->map(function(Offday $offday) {
          return $offday->date->toDateString();
        })
        ->toArray();
  }

}
