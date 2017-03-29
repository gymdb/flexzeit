<?php

namespace Tests\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

trait MockDbTrait {

  protected function mockModel($class, array $attributes = []) {
    $mock = \Mockery::mock($class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $mock->shouldReceive('getAttribute')
        ->andReturnUsing(function($key) use ($attributes) {
          return $attributes[$key];
        });
    return $mock;
  }

  protected function mockResult(Collection $result) {
    $builder = \Mockery::mock(Builder::class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $builder->shouldReceive('get')->andReturn($result);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $builder->shouldReceive('exists')->andReturn(!$result->isEmpty());

    return $builder;
  }

}