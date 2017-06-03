<?php

namespace App\Services;

use App\Helpers\Date;

/**
 * Service for accessing config option values from database while using caches
 *
 * @package App\Services
 */
interface ConfigService {

  /**
   * Invalidate the cache, forcing a reload on the next access
   *
   * @param string|null $key Only invalidate this key
   */
  public function invalidateCache($key = null);

  /**
   * Load the config option associated with the given key
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get($key, $default = null);

  /**
   * Load the config option associated with the given key as a string
   *
   * @param string $key
   * @param string|null $default
   * @return string|null
   */
  public function getAsString($key, $default = null);

  /**
   * Load the config option associated with the given key as an integer
   *
   * @param string $key
   * @param int|null $default
   * @return int|null
   */
  public function getAsInt($key, $default = null);

  /**
   * Load the config option associated with the given key as a datetime object
   *
   * @param string $key
   * @param Date|null $default
   * @return Date|null
   */
  public function getAsDate($key, Date $default = null);

  /**
   * Write the new config value to database
   *
   * @param string $key
   * @param mixed $value
   */
  public function set($key, $value);

  /**
   * Remove a config option
   *
   * @param string $key
   */
  public function destroy($key);

}