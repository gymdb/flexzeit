<?php

namespace App\Exceptions;

class CourseException extends ApiException {

  const EXISTS = 1;
  const SAVE_FAILED = 2;
  const OBLIGATORY_EXISTS = 3;

  /**
   * CourseException constructor.
   *
   * @param int $code One of the defined constants
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

}