<?php

namespace App\Models;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel {

  protected function asDate($value) {
    return $value instanceof Date ? $value : Date::instance($this->asDateTime($value));
  }

}
