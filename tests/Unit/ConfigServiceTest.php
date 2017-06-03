<?php

namespace Tests\Unit;

use App\Helpers\Date;
use App\Models\ConfigOption;
use App\Repositories\ConfigRepository;
use App\Services\ConfigService;
use Mockery;
use Tests\TestCase;
use Tests\Traits\MockDbTrait;

/**
 * Test the ConfigService class
 *
 * @package Tests\Unit
 */
class ConfigTest extends TestCase {

  use MockDbTrait;

  /**
   * @var ConfigService configService
   */
  protected $configService;

  protected function setUp() {
    parent::setUp();
    $this->mock(['repo' => ConfigRepository::class]);
    $this->configService = $this->app->make(ConfigService::class);
  }

  protected function tearDown() {
    $this->configService->invalidateCache();
    parent::tearDown();
  }

  /**
   * Test a set call to the config service
   *
   * @param $key
   * @param $value
   */
  private function setOption($key, $value) {
    $option = Mockery::mock(ConfigOption::class);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $option->shouldReceive('setAttribute')
        ->with('value', $value)
        ->between(1, 1);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $option->shouldReceive('save')
        ->withNoArgs()
        ->between(1, 1);

    $this->shouldReceive('repo', 'findOrNew')
        ->with($key)
        ->between(1, 1)
        ->andReturn($option);

    $this->configService->set($key, $value);
  }

  /**
   * Test a get call to the config service
   *
   * @param $key
   * @param $value
   * @param string $method
   * @param bool $cache Should be returned by cache
   * @return mixed
   */
  private function getOption($key, $value, $method = 'get', $cache = true) {
    if ($cache) {
      $this->shouldReceive('repo', 'find')->never();
    } else {
      $option = $this->mockModel(ConfigOption::class, ['value' => $value]);
      $this->shouldReceive('repo', 'find')
          ->with($key)
          ->between(1, 1)
          ->andReturn($option);
    }

    return $this->{is_object($value) ? 'assertEquals' : 'assertSame'}($value, $this->configService->{$method}($key));
  }

  /**
   * Test a get call to a non-existent option (should return the default value)
   *
   * @param $key
   * @param $default
   * @param string $method
   * @param bool $cache Should be returned by cache
   */
  private function getDefault($key, $default, $method = 'get', $cache = true) {
    if ($cache) {
      $this->shouldReceive('repo', 'find')->never();
    } else {
      $this->shouldReceive('repo', 'find')
          ->with($key)
          ->between(1, 1)
          ->andReturn(null);
    }

    is_null($default)
        ? $this->assertNull($this->configService->{$method}($key))
        : $this->{is_object($default) ? 'assertEquals' : 'assertSame'}($default, $this->configService->{$method}($key, $default));
  }

  /**
   * Test deletion an option value
   *
   * @param $key
   */
  private function destroy($key) {
    $this->shouldReceive('repo', 'destroy')->with($key)->between(1, 1);
    $this->configService->destroy($key);
  }

  public function testSet() {
    $this->setOption('string', 'testString');
    $this->setOption('int', 5);
    $this->setOption('array', [1, 2, 3]);
  }

  public function testGet() {
    $this->getOption('string', 'testString', 'get', false);
    $this->getOption('int', 5, 'get', false);
    $this->getOption('array', [1, 2, 3], 'get', false);
  }

  public function testDestroy() {
    $this->destroy('string');
    $this->destroy('int');
    $this->destroy('array');
  }

  public function testNonExistent() {
    $this->getDefault('nonexistent', null, 'get', false);
    $this->getDefault('nonexistent', null, 'getAsString', false);
    $this->getDefault('nonexistent', null, 'getAsInt', false);
    $this->getDefault('nonexistent', null, 'getAsDate', false);
  }

  public function testDefault() {
    $this->getDefault('nonexistent', 'test', 'get', false);
    $this->getDefault('nonexistent', 'test', 'getAsString', false);
    $this->getDefault('nonexistent', 5, 'getAsInt', false);
    $this->getDefault('nonexistent', Date::today(), 'getAsDate', false);
  }

  public function testDate() {
    $date = Date::create(2017, 12, 03);
    $this->getOption('date_object', $date, 'getAsDate', false);
    $this->getOption('date_string', $date->toDateTimeString(), 'get', false);
    $this->getOption('date_string', $date, 'getAsDate');
    $this->getOption('date_int', $date->timestamp, 'get', false);
    $this->getOption('date_int', $date, 'getAsDate');
  }

  public function testString() {
    $this->getOption('intToString', 5, 'get', false);
    $this->getOption('intToString', '5', 'getAsString');
  }

  public function testInt() {
    $this->getOption('stringToInt', '237', 'get', false);
    $this->getOption('stringToInt', 237, 'getAsInt');
  }

  public function testCache() {
    $this->setOption('cache', 'testString');
    $this->getOption('cache', 'testString', 'get', false);

    $this->setOption('cache2', 'testString2');
    $this->getOption('cache2', 'testString2', 'get', false);

    $this->getOption('cache', 'testString');

    $this->configService->invalidateCache();
    $this->getOption('cache', 'testString', 'get', false);
  }

}
