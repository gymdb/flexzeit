<?php

namespace App\Console\Commands;

use App\Services\OffdayService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class OffdaysCommand extends Command {

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'untis:offdays';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Load offdays from WebUntis';

  /** @var OffdayService */
  private $offdayService;

  public function __construct(OffdayService $lessonService) {
    parent::__construct();
    $this->offdayService = $lessonService;
  }

  /**
   * Execute the console command.
   */
  public function handle() {
    try {
      $this->offdayService->loadOffdays();
      $this->offdayService->loadGroupOffdays();
      Log::notice('untis:offdays executed successfully.');
      $this->line('Loaded holidays and group offdays from WebUntis.');
    } catch (Exception $e) {
      Log::error('Error loading holidays.', ['exception' => $e]);
      $this->error('Error loading holidays: ' . $e->getMessage());
    }
  }

}
