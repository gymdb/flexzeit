<?php

namespace App\Services\Implementation;

use App\Helpers\Date;

trait ServiceTrait {

  protected function matcher(Date $date, $number = null, $cancelled = null) {
    return function($item) use ($date, $number, $cancelled) {
      return $item->date == $date
          && (is_null($number) || is_null($item->number) || $item->number === $number)
          && (is_null($cancelled) || $item->cancelled === $cancelled);
    };
  }

}