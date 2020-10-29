<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use IntlDateFormatter;
use InvalidArgumentException;

/**
 * Carbon instance with the time part set to midnight
 *
 * @package App\Helpers
 */
class Date extends Carbon {

  /** @var IntlDateFormatter */
  public static $formatter;

  protected static $microsecondsFallback = false;

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

  public static function createFromFormat(/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
      $format, $time, $tz = null) {
    $info = date_parse_from_format($format, $time);
    if ($info['error_count'] !== 0 || $info['warning_count'] !== 0) {
      throw new InvalidArgumentException(implode(PHP_EOL, array_merge($info['errors'], $info['warnings'])));
    }

    return parent::createFromFormat($format, $time, $tz);
  }

  public static function createDate($year = null, $month = null, $day = null, $tz = null) {
    return parent::create($year, $month, $day, 0, 0, 0, $tz);
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

  /**
   * Get a Carbon object of this date with a specified time
   *
   * @param string $time
   * @return Carbon
   */
  public function toMyDateTime($time) {
    list($hour, $minute) = explode(':', $time);
    return Carbon::create($this->year, $this->month, $this->day, $hour, $minute, 0, $this->timezone);
  } 

  public function __toString() {
    return static::$formatter->format($this);
  }

}

Date::$formatter = new IntlDateFormatter(config('app.locale'), null, null, null, null, __('messages.format.date'));
