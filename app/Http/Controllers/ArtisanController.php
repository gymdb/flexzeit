<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ArtisanController extends Controller {

  private $allowedCommands = [
      'migrate',
      'schedule:run',
      'untis:absences',
      'untis:offdays',
      'untis:occupations',
      'untis:substitutions'
  ];

  private $clearCommands = [
      'clear-compiled',
      'cache:clear',
      'config:clear',
      'route:clear',
      'view:clear'
  ];

  private $initializeCommands = [
      'package:discover',
      'config:cache',
      'route:cache',
      'storage:link'
  ];

  public function runArtisan(Request $request, string $command) {
    $this->validateRequest($request);
    switch ($command) {
      case 'clear':
        return $this->runMultiple($this->clearCommands);
      case 'initialize':
        return $this->runMultiple($this->initializeCommands);
      default:
        if (!in_array($command, $this->allowedCommands, true)) {
          return ['message' => 'Command not allowed for remote execution.'];
        }
        return ['output' => $this->runCommand($command)];
    }
  }

  private function validateRequest(Request $request) {
    $key = $request->input('key');
    if (!$key || !password_verify($key, config('app.api_key'))) {
      throw new AccessDeniedHttpException('Key is invalid.');
    }
  }

  private function runMultiple(array $commands) {
    return [
        'output' => collect($commands)->mapWithKeys(function($command) {
          return [$command => $this->runCommand($command)];
        })
    ];
  }

  private function runCommand(string $command) {
    Artisan::call($command);
    return Artisan::output();
  }

}
