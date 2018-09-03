<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Middleware for authenticating users through the intranet single-sign-on system
 *
 * @package App\Http\Middleware
 */
class IntranetSignOnMiddleware {

  /** @var array An array mapping (fully qualified) class names to the user type */
  private $types = [
      // TODO The keys should be fully qualified, so it might be necessary to adapt them
      'TeacherSession' => 'teacher',
      'StudentSession' => 'student'
  ];

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $this->doLogin();
    return $next($request);
  }

  /**
   * Execute the actual login logic
   */
  private function doLogin() {
    if (session_status() === PHP_SESSION_NONE) {
      // Start a native PHP session only if it does not exist already
      // We don't need to change anything, so close it again immediately
      session_start(['read_and_close' => true]);
    }

    if (empty($_SESSION['userSession'])) {
      // No intranet session given, do nothing
      return;
    }

    $session = $_SESSION['userSession'];
    if (empty($session->username)) {
      Log::warning('UserSession does not contain a username.');
      return;
    }

    $currentUser = Auth::user();
    if ($currentUser && $currentUser->username === $session->username) {
      // Same user already logged in, do nothing
      return;
    }

    $provider = $this->getUserProvider($session);
    if (!$provider) {
      Log::warning('Could not load UserProvider for session type ' . get_class($session) . '.');
      return;
    }

    $user = $provider->retrieveByCredentials(['username' => $session->username]);
    if (!$user) {
      Log::warning("User for username {$session->username} not found.");
      return;
    }

    Auth::login($user);
  }

  /**
   * @param $session
   * @return UserProvider|null
   */
  private function getUserProvider($session) {
    foreach ($this->types as $class => $type) {
      if ($session instanceof $class) {
        return Auth::createUserProvider($type);
      }
    }
    return null;
  }
}
