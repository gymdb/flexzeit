<?php

namespace App\Console;

use App\Services\ConfigService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
    try {
      $configService = $this->app->make(ConfigService::class);

      $lessons = $configService->getLessonTimes();
      foreach ($lessons as $d => $times) {
        foreach ($times as $n => $time) {
          $schedule->command('untis:absences')
              ->days($d)
              ->at($time['start']);
        }
      }
    } catch (QueryException $e) {
      // This can happen on deployment
      Log::error('Failed scheduling absences loading.', [$e->getMessage()]);
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
