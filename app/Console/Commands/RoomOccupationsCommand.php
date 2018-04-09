<?php

namespace App\Console\Commands;

use App\Services\RoomService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RoomOccupationsCommand extends Command {

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'untis:occupations';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Load room occupations from WebUntis';

  /** @var RoomService */
  private $roomService;

  public function __construct(RoomService $roomService) {
    parent::__construct();
    $this->roomService = $roomService;
  }

  /**
   * Execute the console command.
   */
  public function handle() {
    try {
      $this->roomService->loadRoomOccupations();
      Log::notice('untis:occupations executed successfully.');
      $this->line('Loaded room occupations from WebUntis.');
    } catch (Exception $e) {
      Log::error('Error loading room occupations.', ['exception' => $e]);
      $this->error('Error loading room occupations: ' . $e->getMessage());
    }
  }

}
