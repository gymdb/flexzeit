<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

/**
 * Controller for handling login and logout
 *
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller {

  use AuthenticatesUsers {
    logout as traitLogout;
  }

  /**
   * Create a new controller instance.
   */
  public function __construct() {
    $this->middleware('guest')->except('logout');
  }

  /**
   * Returns the field name used as login name
   *
   * @return string
   */
  public function username() {
    return 'username';
  }

  /**
   * Returns the redirect target after a successful login
   *
   * @return string
   */
  public function redirectPath() {
    $user = $this->guard()->user();
    return route($user instanceof User ? $user->typeString() . '.dashboard' : 'login');
  }

  public function logout(Request $request) {
    $sessionKey = config('services.sso.session_key');
    if ($sessionKey && $request->session()->get('isIntranetAuth')) {
      $requireNewSession = (session_status() === PHP_SESSION_NONE);
      if ($requireNewSession) {
        // Start a native PHP session only if it does not exist already
        // Never try to cleanup sessions
        session_start(['gc_probability' => 0]);
      }

      unset($_SESSION[$sessionKey]);

      if ($requireNewSession) {
        // If PHP session was started just for this close it again
        session_write_close();
      }
    }

    return $this->traitLogout($request);
  }

}
