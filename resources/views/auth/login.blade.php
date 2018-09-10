@extends('layouts.app')

@php $ssoUrl = config('services.sso.url'); @endphp
@section('content')
  <div class="panel panel-default">
    <div class="panel-heading">@lang('auth.login')</div>
    <div class="panel-body">
      @if($ssoUrl)
        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#auth-sso" aria-controls="auth-sso" role="tab" data-toggle="tab">@lang('auth.tabs.sso')</a>
          </li>
          <li>
            <a href="#auth-direct" aria-controls="auth-direct" role="tab" data-toggle="tab">@lang('auth.tabs.direct')</a>
          </li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane active" id="auth-sso">
            <iframe src="{{$ssoUrl}}" class="sso-frame"></iframe>
          </div>

          <div class="tab-pane" id="auth-direct">
            @endif
            <form class="form-horizontal" method="POST" action="{{ route('loginTarget') }}">
              {{ csrf_field() }}

              <div class="form-group{{ $errors->has('username') ? ' has-error' : '' }}">
                <label for="username" class="col-md-4 control-label">@lang('auth.username')</label>

                <div class="col-md-6">
                  <input id="username" class="form-control" name="username" value="{{ old('username') }}" required autofocus>

                  @if ($errors->has('username'))
                    <span class="help-block">
                      <strong>{{ $errors->first('username') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="password" class="col-md-4 control-label">@lang('auth.password')</label>

                <div class="col-md-6">
                  <input id="password" type="password" class="form-control" name="password" required>

                  @if ($errors->has('password'))
                    <span class="help-block">
                      <strong>{{ $errors->first('password') }}</strong>
                    </span>
                  @endif
                </div>
              </div>

              {{--<div class="form-group">--}}
              {{--<div class="col-md-6 col-md-offset-4">--}}
              {{--<div class="checkbox">--}}
              {{--<label>--}}
              {{--<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me--}}
              {{--</label>--}}
              {{--</div>--}}
              {{--</div>--}}
              {{--</div>--}}

              <div class="form-group">
                <div class="col-md-8 col-md-offset-4">
                  <button class="btn btn-primary">@lang('auth.login')</button>

                  {{--<a class="btn btn-link" href="{{ route('password.request') }}">--}}
                  {{--Forgot Your Password?--}}
                  {{--</a>--}}
                </div>
              </div>

            </form>
            @if($ssoUrl)
          </div>
        </div>
      @endif

      <div class="text-center">
        <img src="{{asset('images/bottom.png')}}" class="login-image col-md-8 col-md-push-2"/>
      </div>
    </div>
  </div>
@endsection
