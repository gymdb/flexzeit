<?php

namespace App\Http\Middleware;

use __PHP_Incomplete_Class;
use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use StudentSession;
use TeacherSession;

/**
 * Middleware for authenticating users through the intranet single-sign-on system
 *
 * @package App\Http\Middleware
 */
class IntranetSignOnMiddleware {

  /** @var array An array mapping (fully qualified) class names to the user type */
  private $types = [
      TeacherSession::class => 'teacher',
      StudentSession::class => 'student'
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
      // We don't need to change anything, so close it again immediately, and never try to cleanup sessions
      session_start(['read_and_close' => true, 'gc_probability' => 0]);
    }

    if (empty($_SESSION['userSession'])) {
      // No intranet session given, call logout logic
      $this->doLogout();
      return;
    }

    $session = $_SESSION['userSession'];
    if ($session instanceof __PHP_Incomplete_Class) {
      // This might be caused by a change in how logout is handled at the SSO application
      Log::warning('UserSession has unknown type ' . ((array)$session)['__PHP_Incomplete_Class_Name']);
      $this->doLogout();
      return;
    }

    if (empty($session->username)) {
      Log::warning('UserSession does not contain a username.');
      $this->doLogout();
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
      $this->doLogout();
      return;
    }

    $user = $provider->retrieveByCredentials(['username' => $session->username]);
    if (!$user) {
      Log::warning("User for username {$session->username} not found.");
      $this->doLogout();
      return;
    }

    Auth::login($user);
    if ($laravelSession = $this->getLaravelSession()) {
      $laravelSession->put('isIntranetAuth', true);
    }
  }

  private function doLogout() {
    $session = $this->getLaravelSession();
    if ($session && !$session->get('isIntranetAuth')) {
      // Session is not marked as authenticated through this middleware, do nothing
      return;
    }

    Auth::logout();
    if ($session) {
      $session->remove('isIntranetAuth');
    }
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

  /**
   * @return Session|null
   */
  private function getLaravelSession() {
    $guard = Auth::guard();
    if (!$guard instanceof SessionGuard) {
      Log::warning('SessionGuard is not used, there may be problems on logout.');
      return null;
    }
    return $guard->getSession();
  }
}
