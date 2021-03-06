<?php

namespace App\Exceptions;

class CourseException extends ApiException {

  const EXISTS = 1;
  const SAVE_FAILED = 2;
  const OBLIGATORY_EXISTS = 3;
  const EDIT_SPEC = 4;
  const EDIT_PERIOD = 5;
  const EDIT_GROUPS = 6;
  const DELETE_PERIOD = 7;
  const NOT_IN_TIMETABLE = 8;
  const OBLIGATORY_OFFDAY = 9;

}
