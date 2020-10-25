<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler {

  /**
   * A list of the exception types that are not reported.
   *
   * @var array
   */
  protected $dontReport = [
      ApiException::class
  ];

  /**
   * Render the given HttpException.
   *
   * @param  HttpException $e
   * @return Response
   */
  protected function renderHttpException(HttpExceptionInterface $e) {
    $status = $e->getStatusCode();
    return response()->view('error', ['message' => $e->getMessage(), 'status' => $status], $status, $e->getHeaders());
  }

}
