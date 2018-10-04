<?php

namespace App\Helpers\Anacron;

use Illuminate\Console\Scheduling\Schedule as BaseSchedule;

class Schedule extends BaseSchedule {

  /**
   * Add a new command event to the schedule.
   *
   * @param  string $command
   * @param  array $parameters
   * @return \Illuminate\Console\Scheduling\Event
   */
  public function exec($command, array $parameters = []) {
    if (count($parameters)) {
      $command .= ' ' . $this->compileParameters($parameters);
    }

    $this->events[] = $event = new Event($this->mutex, $command);

    return $event;
  }

  /**
   * Get all of the events on the schedule that are due.
   *
   * @param  \Illuminate\Contracts\Foundation\Application $app
   * @return \Illuminate\Support\Collection
   */
  public function dueEvents($app) {
    return parent::dueEvents($app)->unique('command');
  }

}
