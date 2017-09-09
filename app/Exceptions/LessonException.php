<?php

namespace App\Exceptions;

class LessonException extends ApiException {

  const CANCEL_PERIOD = 30;
  const SAME_TEACHER = 31;
  const CANCELLED = 32;

}
