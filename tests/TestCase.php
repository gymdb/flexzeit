<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery;
use Mockery\Expectation;

abstract class TestCase extends BaseTestCase {

  use CreatesApplication;

  private $mocks = [];

  protected function tearDown() {
    Mockery::close();
    parent::tearDown();
  }

  protected function mock(array $classes) {
    foreach ($classes as $key => $options) {
      if (empty($this->mocks[$key])) {
        $class = is_array($options) ? $options[0] : $options;
        $this->mocks[$key] = is_array($options)
            ? Mockery::mock($class . $options[1], $options[2])
            : Mockery::mock($class);
        $this->app->instance($class, $this->mocks[$key]);
      }
    }
  }

  protected function getMocked($key) {
    return empty($this->mocks[$key]) ? null : $this->mocks[$key];
  }

  /**
   * @param string $key
   * @param string $method
   * @return Expectation
   */
  protected function shouldReceive($key, $method) {
    return $this->mocks[$key]->shouldReceive($method);
  }

}
