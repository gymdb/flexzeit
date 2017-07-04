<?php

namespace App\Http;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Routing\Redirector as BaseRedirector;
use Illuminate\Support\Facades\Auth;

/**
 * Extension of Laravel's default redirector considering the user type on login redirects
 *
 * @package App\Http
 */
class Redirector extends BaseRedirector {

  public function intended($default = '/', $status = 302, $headers = [], $secure = null) {
    $path = $this->session->pull('url.intended', $default);

    $user = Auth::user();
    if ((str_contains(strtolower($path), '/teacher') && !($user instanceof Teacher))
        || (str_contains(strtolower($path), '/student') && !($user instanceof Student))
    ) {
      $path = $default;
    }

    return $this->to($path, $status, $headers, $secure);
  }

}
