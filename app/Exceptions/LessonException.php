<?php

namespace App\Exceptions;

class LessonException extends ApiException {

  const CANCEL_PERIOD = 30;

  /**
   * CourseException constructor.
   *
   * @param int $code One of the defined constants
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

}