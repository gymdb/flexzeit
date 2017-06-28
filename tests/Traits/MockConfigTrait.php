<?php
namespace Tests\Traits;

use App\Repositories\ConfigRepository;
use App\Services\Implementation\ConfigStorageServiceImpl;
use Mockery;

trait MockConfigTrait {

  private $mockedConfig = [];

  protected abstract function mock(array $classes);

  protected function mockConfig(array $config) {
    $this->mock(['configService' => [ConfigStorageServiceImpl::class, '[get]', [Mockery::mock(ConfigRepository::class)]]]);
    $this->mockedConfig = array_merge($this->mockedConfig, $config);
    $this->shouldReceive('configService', 'get')
        ->andReturnUsing(function($key) {
          return isset($this->mockedConfig[$key]) ? $this->mockedConfig[$key] : null;
        });
  }

}