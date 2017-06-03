<?php

namespace App\Exceptions;

class CourseException extends ApiException {

  const DATE_NOT_IN_YEAR = 1;
  const CREATE_PERIOD_ENDED = 2;
  const OFFDAY = 3;
  const INVALID_END_DATE = 4;
  const EXISTS = 5;
  const SAVE_FAILED = 6;
  const OBLIGATORY_EXISTS = 7;
  const NOT_FOUND = 8;

  /**
   * CourseException constructor.
   *
   * @param int $code One of the defined constants
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

}