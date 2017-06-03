<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParametersMiddleware {

  /**
   * Handle an incoming request.
   *
   * @param  Request $request
   * @param  Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next, $keys) {
    $route = $request->route();
    foreach (explode('|', $keys) as $options) {
      $options = explode(';', $options, 2);
      $key = $options[0];
      $type = count($options) >= 2 ? $options[1] : null;

      $required = true;
      if (substr($key, -1) === '?') {
        $required = false;
        $key = substr($key, 0, -1);
      }

      if ($request->has($key)) {
        $value = $request->input($key);
        switch ($type) {
          case 'i':
            if (!is_int($value) && !ctype_digit($value)) {
              throw new NotFoundHttpException('Invalid integer value for ' . $key);
            }
            $value = (int)$value;
            break;
          case 'b':
            if (!is_bool($value) && $value !== 0 && $value !== 1 && $value !== '0' && $value !== '1') {
              throw new NotFoundHttpException('Invalid boolean value for ' . $key);
            }
            $value = ($value === true || $value === 1 || $value === '1');
            break;
          case 'd':
            if (!preg_match('/^20\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/', $value)) {
              throw new NotFoundHttpException('Invalid date value for ' . $key);
            }
            break;
        }

        $route->setParameter($key, $value);
      } else if ($required) {
        throw new NotFoundHttpException('Missing required parameter ' . $key);
      }
    }
    return $next($request);
  }
}
