<?php

namespace App\Helpers;

class DateConstraints {

  /** @var Date $firstDate The first date for the range */
  private $firstDate;

  /** @var Date|null $lastDate The last date for the range */
  private $lastDate;

  /** @var int|null $dayOfWeek 0=Sunday to 6=Saturday */
  private $dayOfWeek;

  /** @var int|null $frequency Repetition frequency in weeks */
  private $frequency;

  /** @var int[]|null $numbers The lesson numbers that should be included */
  private $numbers;

  /**
   * @param Date $firstDate
   * @param Date|null $lastDate
   * @param int|int[]|null $numbers
   * @param int|null $frequency
   */
  public function __construct(Date $firstDate, Date $lastDate = null, $numbers = null, int $frequency = null) {
    $this->firstDate = $firstDate;
    $this->lastDate = $lastDate;
    $this->dayOfWeek = $lastDate && $frequency ? $firstDate->dayOfWeek : null;
    $this->frequency = $lastDate && $frequency ? $frequency : null;
    $this->numbers = $numbers ? (array)$numbers : null;
  }

  /**
   * @return Date
   */
  public function getFirstDate(): Date {
    return $this->firstDate;
  }

  /**
   * @return Date|null
   */
  public function getLastDate() {
    return $this->lastDate;
  }

  /**
   * @return int|null
   */
  public function getDayOfWeek() {
    return $this->dayOfWeek;
  }

  /**
   * @return int|null
   */
  public function getFrequency() {
    return $this->frequency;
  }

  /**
   * @return int[]|null
   */
  public function getNumbers() {
    return $this->numbers;
  }

  /**
   * @return DateRange
   */
  public function getDateRange() {
    return new DateRange($this->firstDate, $this->lastDate, $this->dayOfWeek, $this->frequency);
  }
}
