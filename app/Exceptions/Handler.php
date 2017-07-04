<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler {

  /**
   * A list of the exception types that should not be reported.
   *
   * @var array
   */
  protected $dontReport = [
      AuthenticationException::class,
      AuthorizationException::class,
      HttpException::class,
      ModelNotFoundException::class,
      TokenMismatchException::class,
      ValidationException::class,
  ];

  /**
   * Report or log an exception.
   *
   * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
   *
   * @param  Exception $exception
   * @return void
   */
  public function report(Exception $exception) {
    parent::report($exception);
  }

  /**
   * Render an exception into an HTTP response.
   *
   * @param  Request $request
   * @param  Exception $exception
   * @return Response
   */
  public function render($request, Exception $exception) {
    if ($exception instanceof ApiException) {
      if ($request->expectsJson()) {
        return response()->json(['success' => false, 'error' => $exception->getCode()]);
      }

      return redirect()->back()->withInput($request->input())->withErrors(['api' => [$exception->getCode()]]);
    }

    return parent::render($request, $exception);
  }

  protected function prepareException(Exception $e) {
    if ($e instanceof TokenMismatchException) {
      $e = new HttpException(403, 'Security token mismatch', $e);
    }

    return parent::prepareException($e);
  }

  protected function prepareResponse($request, Exception $e) {
    if ($request->expectsJson()) {
      return response()->json(['message' => $e->getMessage()], ($e instanceof HttpException) ? $e->getStatusCode() : 500);
    }
    return parent::prepareResponse($request, $e);
  }

  /**
   * Convert an authentication exception into an unauthenticated response.
   *
   * @param  Request $request
   * @param  AuthenticationException $exception
   * @return \Illuminate\Http\Response
   */
  protected function unauthenticated($request, AuthenticationException $exception) {
    if ($request->expectsJson()) {
      return response()->json(['message' => $exception->getMessage()], 401);
    }

    return redirect()->guest('login');
  }
}
