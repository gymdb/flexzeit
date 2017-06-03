<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware for redirecting already authenticated users
 *
 * @package App\Http\Middleware
 */
class RedirectIfAuthenticated {

  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure $next
   * @param  string|null $guard
   * @return mixed
   */
  public function handle($request, Closure $next, $guard = null) {
    if (Auth::guard($guard)->check()) {
      // User is logged in: Redirect to correct page

      /** @var User $user */
      $user = Auth::guard($guard)->user();
      return redirect()->route($user ? $user->typeString() . '.dashboard' : 'login');
    }

    return $next($request);
  }
}
