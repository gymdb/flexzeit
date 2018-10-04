<?php

namespace App\Console\Commands;

use App\Services\SubstitutionService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubstitutionsCommand extends Command {

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'untis:substitutions';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Load substitutions from WebUntis';

  /** @var SubstitutionService */
  private $substitutionService;

  public function __construct(SubstitutionService $substitutionService) {
    parent::__construct();
    $this->substitutionService = $substitutionService;
  }

  /**
   * Execute the console command.
   */
  public function handle() {
    try {
      $this->substitutionService->loadSubstitutions();
      Log::notice('untis:substitutions executed successfully.');
      $this->line('Loaded substitutions from WebUntis.');
    } catch (Exception $e) {
      Log::error('Error loading substitutions.', ['exception' => $e]);
      $this->error('Error loading substitutions: ' . $e->getMessage());
    }
  }

}
