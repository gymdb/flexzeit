<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

/**
 * Middleware for wrapping actions in a database transaction
 *
 * @package App\Http\Middleware
 */
class TransactionMiddleware {

  /**
   * Handle an incoming request.
   *
   * @param  Request $request
   * @param  Closure $next
   * @return mixed
   * @throws Exception
   */
  public function handle(Request $request, Closure $next) {
    DB::beginTransaction();

    try {
      $response = $next($request);
    } catch (Exception $e) {
      DB::rollBack();
      throw $e;
    }

    if ($response instanceof Response && $response->getStatusCode() >= 400) {
      DB::rollBack();
    } else {
      DB::commit();
    }

    return $response;
  }
}
