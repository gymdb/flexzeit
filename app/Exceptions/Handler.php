<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler {

  /**
   * Render the given HttpException.
   *
   * @param  HttpException $e
   * @return Response
   */
  protected function renderHttpException(HttpException $e) {
    $status = $e->getStatusCode();
    return response()->view('error', ['message' => $e->getMessage(), 'status' => $status], $status, $e->getHeaders());
  }

}
