<?php

namespace App\Http\Middleware;

use App\Helpers\Date;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Middleware for resolving Date parameters
 *
 * @package App\Http\Middleware
 */
class DateMiddleware {

  /**
   * Handle an incoming request.
   *
   * @param  Request $request
   * @param  Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    $route = $request->route();
    $parameters = $route->parameters();

    foreach ($route->signatureParameters(Carbon::class) as $parameter) {
      if (!$route->parameter($parameter->name) instanceof Date) {
        if (empty($parameters[$parameter->name])) {
          if (!$parameter->isDefaultValueAvailable()) {
            throw new NotFoundHttpException("Missing parameter " . $parameter->name);
          }
        } else {
          try {
            $route->setParameter($parameter->name, Date::createFromFormat('Y-m-d', $parameters[$parameter->name]));
          } catch (InvalidArgumentException $ex) {
            throw new NotFoundHttpException("Invalid date for parameter " . $parameter->name, $ex);
          }
        }
      }
    }

    return $next($request);
  }
}
