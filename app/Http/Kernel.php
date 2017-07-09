<?php

namespace App\Http;

use App\Http\Middleware\DateMiddleware;
use App\Http\Middleware\ParametersMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\StripTags;
use App\Http\Middleware\TransactionMiddleware;
use App\Http\Middleware\TrimStrings;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel {

  /**
   * The application's global HTTP middleware stack.
   *
   * These middleware are run during every request to your application.
   *
   * @var array
   */
  protected $middleware = [
      CheckForMaintenanceMode::class,
      ValidatePostSize::class,
      StripTags::class,
      TrimStrings::class,
      ConvertEmptyStringsToNull::class,
  ];

  /**
   * This specifies the order in which the given middleware must run
   *
   * @var array
   */
  protected $middlewarePriority = [
      ParametersMiddleware::class,
      SubstituteBindings::class,
      DateMiddleware::class
  ];

  /**
   * The application's route middleware groups.
   *
   * @var array
   */
  protected $middlewareGroups = [
      'web' => [
          EncryptCookies::class,
          AddQueuedCookiesToResponse::class,
          StartSession::class,
          AuthenticateSession::class,
          ShareErrorsFromSession::class,
          VerifyCsrfToken::class,
          SubstituteBindings::class,
          DateMiddleware::class
      ],

      'api' => [
          'throttle:60,1',
          'bindings',
      ],
  ];

  /**
   * The application's route middleware.
   *
   * These middleware may be assigned to groups or used individually.
   *
   * @var array
   */
  protected $routeMiddleware = [
      'auth'        => Authenticate::class,
      'auth.basic'  => AuthenticateWithBasicAuth::class,
      'bindings'    => SubstituteBindings::class,
      'can'         => Authorize::class,
      'guest'       => RedirectIfAuthenticated::class,
      'throttle'    => ThrottleRequests::class,
      'params'      => ParametersMiddleware::class,
      'transaction' => TransactionMiddleware::class
  ];
}
