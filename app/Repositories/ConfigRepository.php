<?php

namespace App\Repositories;

use App\Models\ConfigOption;

/**
 * Repository for loading config options
 *
 * @package App\Repository
 */
interface ConfigRepository {

  /**
   * @param string $key
   * @return ConfigOption|null
   */
  public function find($key);

  /**
   * @param string $key
   * @return ConfigOption
   */
  public function findOrNew($key);

  /**
   * @param string $key
   * @return void
   */
  public function destroy($key);

}
