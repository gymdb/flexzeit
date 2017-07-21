<?php

namespace App\Exceptions;

class BugReportException extends ApiException {

  const INVALID_TEXT = 40;
  const INVALID_USER = 41;

  /**
   * LessonException constructor.
   *
   * @param int $code One of the defined constants
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

}
