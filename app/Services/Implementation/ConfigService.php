<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Repositories\ConfigRepository;
use DateTime;
use Illuminate\Support\Facades\Cache;

/**
 * Service for accessing config option values from database while using caches
 *
 * @package App\Services
 */
class ConfigService implements \App\Services\ConfigService {

  private $configRepository;

  private $prefix = 'config';

  function __construct(ConfigRepository $configRepository) {
    $this->configRepository = $configRepository;
  }

  public function invalidateCache($key = null) {
    if (is_null($key)) {
      $this->getCache()->flush();
    } else {
      $this->getCache()->forget($this->prefix . '.' . $key);
    }
  }

  public function get($key, $default = null) {
    $value = $this->getCache()->rememberForever($this->prefix . '.' . $key, function() use ($key) {
      $configOption = $this->configRepository->find($key);
      return $configOption ? $configOption->value : null;
    });
    return is_null($value) ? $default : $value;
  }

  public function getAsString($key, $default = null) {
    $value = $this->get($key);
    return is_null($value) ? $default : (string)$value;
  }

  public function getAsInt($key, $default = null) {
    $value = $this->get($key);
    return is_null($value) ? $default : (int)$value;
  }

  public function getAsDate($key, Date $default = null) {
    $value = $this->get($key);
    if (is_null($value)) {
      return $default;
    }
    if ($value instanceof DateTime) {
      return Date::instance($value);
    }
    if (is_int($value)) {
      return Date::createFromTimestampUTC($value);
    }
    $tz = null;
    if (is_array($value) && isset($value['date'])) {
      if (isset($value['timezone'])) {
        $tz = $value['timezone'];
      }
      $value = $value['date'];
    }
    if (is_string($value)) {
      return Date::parse($value, $tz);
    }

    return null;
  }

  public function set($key, $value) {
    $configOption = $this->configRepository->findOrNew($key);
    $configOption->value = $value instanceof DateTime ? $value->format('c') : $value;
    $configOption->save();
    $this->invalidateCache($key);
  }

  public function destroy($key) {
    $this->configRepository->destroy($key);
    $this->invalidateCache($key);
  }

  protected function getCache() {
    return Cache::tags($this->prefix);
  }

}