<?php

namespace App\Helpers\Anacron;

use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event as BaseEvent;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class Event extends BaseEvent {

  /**
   * Determine if the Cron expression passes.
   *
   * @return bool
   */
  protected function expressionPasses() {
    $date = Carbon::now();

    if ($this->timezone) {
      $date->setTimezone($this->timezone);
    }

    $lastRun = Cache::get($this->cacheKey());
    if (!$lastRun) {
      return true;
    }

    return CronExpression::factory($this->expression)->getPreviousRunDate($date->toDateTimeString(), 0, true) > $lastRun;
  }

  public function run(Container $container) {
    Cache::forever($this->cacheKey(), Carbon::now());
    parent::run($container);
  }

  private function cacheKey() {
    return 'schedule.' . sha1($this->command);
  }

}
