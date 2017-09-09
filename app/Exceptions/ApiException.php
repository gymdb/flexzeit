<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;

class ApiException extends Exception {

  /**
   * @param int $code One of the defined constants in the subclasses
   */
  public function __construct($code) {
    parent::__construct("", $code);
  }

  /**
   * Render the exception into an HTTP response.
   *
   * @param Request $request
   * @return Response
   */
  public function render(Request $request) {
    if ($request->expectsJson()) {
      return response()->json(['success' => false, 'error' => $this->getCode()]);
    }

    return redirect()->back()->withInput($request->input())->withErrors(['api' => [Lang::get('errors.' . $this->getCode())]]);
  }

}
