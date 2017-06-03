<?php

namespace App\Helpers;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Carbon instance with the time part set to midnight
 *
 * @package App\Helpers
 */
class Date extends Carbon {

  protected static $toStringFormat = 'd.m.Y';

  public function __construct($time = null, $tz = null) {
    parent::__construct($time, $tz);
    $this->startOfDay();
  }

  public static function checkedCreate($dateString) {
    try {
      return static::createFromFormat('Y-m-d', $dateString);
    } catch (InvalidArgumentException $ex) {
      return null;
    }
  }

  public static function createFromFormat($format, $time, $tz = null) {
    $info = date_parse_from_format($format, $time);
    if ($info['error_count'] !== 0 || $info['warning_count'] !== 0) {
      throw new InvalidArgumentException(implode(PHP_EOL, array_merge($info['errors'], $info['warnings'])));
    }

    return parent::createFromFormat($format, $time, $tz);
  }

  /**
   * Set to next date with given dayOfWeek
   *
   * @param int $dayOfWeek
   * @return static
   */
  public function setToDayOfWeek($dayOfWeek) {
    $dayOfWeek %= 7;
    return $this->modify(static::$days[$dayOfWeek >= 0 ? $dayOfWeek : 7 + $dayOfWeek]);
  }

}