<?php

namespace App\Exceptions;

class RegistrationException extends ApiException {

  const OFFDAY = 20;
  const MAXSTUDENTS = 21;
  const HAS_COURSE = 22;
  const ALREADY_REGISTERED = 23;
  const REGISTRATION_PERIOD = 24;
  const OBLIGATORY = 25;
  const YEAR = 26;
  const INVALID_ATTENDANCE = 27;
  const ATTENDANCE_PERIOD = 28;
  const INVALID_FEEDBACK = 29;
  const FEEDBACK_PERIOD = 30;

  /**
   * LessonException constructor.
   *
   * @param int $code One of the defined constants
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

}