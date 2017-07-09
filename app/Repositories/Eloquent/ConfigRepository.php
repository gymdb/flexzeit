<?php

namespace App\Repositories\Eloquent;

use App\Models\ConfigOption;

class ConfigRepository implements \App\Repositories\ConfigRepository {

  public function find($key) {
    return ConfigOption::find($key);
  }

  public function findOrNew($key) {
    return ConfigOption::find($key) ?: new ConfigOption(['key' => $key]);
  }

  public function destroy($key) {
    /** @var ConfigOption $option */
    $option = ConfigOption::find($key);
    if ($option) {
      $option->delete();
    }
  }
}
