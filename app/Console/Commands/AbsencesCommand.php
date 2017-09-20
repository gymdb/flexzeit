<?php

namespace App\Console\Commands;

use App\Helpers\Date;
use App\Services\StudentService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AbsencesCommand extends Command {

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'untis:absences {date?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Load today\'s absences from WebUntis';

  /** @var StudentService */
  protected $studentService;

  public function __construct(StudentService $studentService) {
    parent::__construct();
    $this->studentService = $studentService;
  }

  /**
   * Load the absences for a given day
   */
  public function handle() {
    try {
      $dateString = $this->argument('date');
      $date = $dateString ? Date::createFromFormat('Y-m-d', $dateString) : Date::today();
      $this->studentService->loadAbsences($date);
      Log::notice('untis:absences ' . $date->toDateString() . ' executed successfully.');
      $this->line('Loaded absences for ' . $date->toDateString());
    } catch (Exception $e) {
      Log::error('Error loading absences.', ['exception' => $e]);
      $this->error('Error loading absences: ' . $e->getMessage());
    }
  }

}
