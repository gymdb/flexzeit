<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;

/**
 * Middleware for adding cache control headers
 *
 * @package App\Http\Middleware
 */
class CacheControlMiddleware {

  /**
   * Handle an incoming request.
   *
   * @param  Request $request
   * @param  Closure $next
   * @return mixed
   * @throws Exception
   */
  public function handle(Request $request, Closure $next) {
    $response = $next($request);
    $response->headers->addCacheControlDirective('no-store,must-revalidate');
    return $response;
  }
}
