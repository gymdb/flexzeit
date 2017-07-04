<?php

namespace App\Console\Commands;

use App\Services\OffdayService;
use Exception;
use Illuminate\Console\Command;

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

  public function __construct(OffdayService $offdayService) {
    parent::__construct();
    $this->offdayService = $offdayService;
  }

  /**
   * Execute the console command.
   */
  public function handle() {
    try {
      $this->offdayService->loadOffdays();
      $this->line('Loaded holidays from WebUntis.');
    } catch (Exception $e) {
      $this->error('Error loading holidays: ' . $e->getMessage());
    }
  }

}
