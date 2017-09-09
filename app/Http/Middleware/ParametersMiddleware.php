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
   * @param string $keys
   * @return mixed
   */
  public function handle(Request $request, Closure $next, $keys) {
    $route = $request->route();
    foreach (explode('|', $keys) as $options) {
      $options = explode(';', $options, 2);
      $key = $options[0];
      $type = count($options) >= 2 ? $options[1] : null;

      $required = true;
      $array = false;
      if (substr($key, -1) === '*') {
        $array = true;
      }
      if ($array || substr($key, -1) === '?') {
        $required = false;
        $key = substr($key, 0, -1);
      }

      if ($request->filled($key)) {
        $data = $request->input($key);

        if (!$array) {
          $data = [$data];
        } else if (!is_array($data)) {
          throw new NotFoundHttpException('Invalid array value for ' . $key);
        }

        foreach ($data as $i => $value) {
          switch ($type) {
            case 'i':
              if (!is_int($value) && !ctype_digit($value)) {
                throw new NotFoundHttpException('Invalid integer value for ' . $key);
              }
              $data[$i] = (int)$value;
              break;
            case 'b':
              if (!is_bool($value) && $value !== 0 && $value !== 1 && $value !== '0' && $value !== '1') {
                throw new NotFoundHttpException('Invalid boolean value for ' . $key);
              }
              $data[$i] = ($value === true || $value === 1 || $value === '1');
              break;
            case 'd':
              if (!preg_match('/^20\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/', $value)) {
                throw new NotFoundHttpException('Invalid date value for ' . $key);
              }
              break;
          }
        }

        $route->setParameter($key, $array ? $data : $data[0]);
      } else if ($required) {
        throw new NotFoundHttpException('Missing required parameter ' . $key);
      }
    }
    return $next($request);
  }
}
