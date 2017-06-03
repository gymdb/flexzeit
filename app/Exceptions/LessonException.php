<?php

namespace App\Exceptions;

class LessonException extends ApiException {

  const DAY_OF_WEEK = 10;
  const NUMBERS = 11;

  /**
   * LessonException constructor.
   *
   * @param int $code One of the defined constants
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

}