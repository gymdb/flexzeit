<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;

/**
 * Controller for handling login and logout
 *
 * @package App\Http\Controllers\Auth
 */
class LoginController extends Controller {

  use AuthenticatesUsers;

  /**
   * Create a new controller instance.
   */
  public function __construct() {
    $this->middleware('guest', ['except' => 'logout']);
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

}
