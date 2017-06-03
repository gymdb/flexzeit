<?php

namespace Tests\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Mockery;

trait MockDbTrait {

  protected function mockModel($class, array $attributes = [], array $associate = []) {
    $mock = Mockery::mock($class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $mock->shouldReceive('getAttribute')
        ->andReturnUsing(function($key) use ($attributes) {
          return $attributes[$key];
        });

    foreach ($associate as $name => $model) {
      $relation = Mockery::mock(Relation::class);
      /** @noinspection PhpMethodParametersCountMismatchInspection */
      $mock->shouldReceive($name)->andReturn($relation);
      /** @noinspection PhpMethodParametersCountMismatchInspection */
      $relation->shouldReceive('associate')
          ->between(1, 1)
          ->with($model);
    }

    return $mock;
  }

  protected function mockResult(Collection $result) {
    $builder = Mockery::mock(Builder::class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $builder->shouldReceive('get')->andReturn($result);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $builder->shouldReceive('exists')->andReturn(!$result->isEmpty());

    return $builder;
  }

}