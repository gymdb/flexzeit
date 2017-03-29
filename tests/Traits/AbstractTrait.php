<?php

namespace Tests\Traits;

use Illuminate\Support\Collection;

trait AbstractTrait {

  protected abstract function mockModel($class, array $attributes = []);

  protected abstract function mockResult(Collection $result);

  protected abstract function mock(array $classes);

  protected abstract function shouldReceive($key, $method);

}