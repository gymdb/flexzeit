<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ViewGlobalsMiddleware {

  /**
   * Handle an incoming request.
   *
   * @param  Request $request
   * @param  Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    View::share('global', [
        'csrfToken' => csrf_token(),
        'baseURL'   => url('/'),
        'lang'      => config('app.locale')
    ]);

    return $next($request);
  }
}
