<?php

namespace App\Console;

use App\Services\ConfigService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
      Commands\AbsencesCommand::class,
      Commands\OffdaysCommand::class
  ];

  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule) {
    $configService = $this->app->make(ConfigService::class);

    $lessons = $configService->getLessonTimes();
    foreach ($lessons as $d => $times) {
      foreach ($times as $n => $time) {
        $schedule->command('untis:absences')
            ->days($d)
            ->at($time['start']);
      }
    }

    $schedule->command('untis:offdays')
        ->twiceDaily(6, 18);
  }

  /**
   * Register the Closure based commands for the application.
   *
   * @return void
   */
  protected function commands() {
    /** @noinspection PhpIncludeInspection */
    require base_path('routes/console.php');
  }
}
