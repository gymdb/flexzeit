<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class StripTags extends TransformsRequest {

  /**
   * The attributes that should not be stripped.
   *
   * @var array
   */
  protected $except = [];

  /**
   * Transform the given value.
   *
   * @param  string $key
   * @param  mixed $value
   * @return mixed
   */
  protected function transform($key, $value) {
    if (in_array($key, $this->except, true)) {
      return $value;
    }

    return is_string($value) ? strip_tags($value) : $value;
  }
}
