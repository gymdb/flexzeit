<?php

namespace App\Services\Implementation;

use App\Repositories\ConfigRepository;
use Carbon\Carbon;
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
      Cache::tags($this->prefix)->flush();
    } else {
      Cache::tags($this->prefix)->forget($this->prefix . '.' . $key);
    }
  }

  public function get($key, $default = null) {
    return Cache::tags($this->prefix)->rememberForever($this->prefix . '.' . $key, function() use ($key) {
      $configOption = $this->configRepository->find($key);
      return $configOption ? $configOption->value : null;
    }) ?: $default;
  }

  public function getAsString($key, $default = null) {
    $value = $this->get($key);
    return is_null($value) ? $default : (string)$value;
  }

  public function getAsInt($key, $default = null) {
    $value = $this->get($key);
    return is_null($value) ? $default : (int)$value;
  }

  public function getAsDate($key, $default = null) {
    $value = $this->get($key);
    if (is_null($value)) {
      return $default;
    }
    if ($value instanceof \DateTime) {
      return Carbon::instance($value);
    }
    if (is_array($value) && isset($value['date'])) {
      return isset($value['timezone']) ? new Carbon($value['date'], $value['timezone']) : new Carbon($value['date']);
    }
    if (is_int($value)) {
      return Carbon::createFromTimestampUTC($value);
    }
    if (is_string($value)) {
      return Carbon::parse($value);
    }

    return null;
  }

  public function set($key, $value) {
    $configOption = $this->configRepository->findOrNew($key);
    $configOption->value = $value instanceof \DateTime ? $value->format('c') : $value;
    $configOption->save();
    $this->invalidateCache($key);
  }

  public function destroy($key) {
    $this->configRepository->destroy($key);
    $this->invalidateCache($key);
  }

}