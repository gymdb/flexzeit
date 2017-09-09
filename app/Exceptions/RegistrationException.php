<?php

namespace App\Exceptions;

class RegistrationException extends ApiException {

  const OFFDAY = 10;
  const MAXSTUDENTS = 11;
  const HAS_COURSE = 12;
  const ALREADY_REGISTERED = 13;
  const REGISTRATION_PERIOD = 14;
  const OBLIGATORY = 15;
  const YEAR = 16;
  const INVALID_ATTENDANCE = 20;
  const ATTENDANCE_PERIOD = 21;
  const INVALID_FEEDBACK = 22;
  const FEEDBACK_PERIOD = 23;
  const INVALID_DOCUMENTATION = 24;
  const DOCUMENTATION_PERIOD = 25;

}
