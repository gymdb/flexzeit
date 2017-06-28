<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- CSRF Token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Flexzeit') }}</title>
  <link rel="icon" href="data:,">

  <!-- Styles -->
  <link href="{{ asset('css/app.css') }}" rel="stylesheet">

  <!-- Scripts -->
  <script>
    window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'baseURL' => url('/')
        ]) !!};
  </script>
</head>
<body>
<div id="app">
  <nav class="navbar navbar-default navbar-static-top">
    <div class="container">
      @if(Auth::guest())
        <div class="navbar-header">
          <a class="navbar-brand" href="{{url('/')}}">
            {{ config('app.name', 'Flexzeit') }}
          </a>
        </div>
      @else
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse"
                  title="@lang('messages.navToggle')">
            <span class="sr-only">@lang('messages.navToggle')</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>

          <a class="navbar-brand" href="{{Auth::user()->isStudent() ? route('student.dashboard') : route('teacher.dashboard')}}">
            {{ config('app.name', 'Flexzeit') }}
          </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
          @if(Auth::user()->isTeacher())
            <ul class="nav navbar-nav">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                  @lang('lessons.lessons') <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                  <li>
                    <a href="{{route('teacher.lessons.index')}}">@lang('lessons.my')</a>
                    <a href="{{route('teacher.courses.create')}}">@lang('courses.create')</a>
                  </li>
                </ul>
              </li>

              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                  @lang('messages.reports') <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                  <li>
                    <a href="{{route('teacher.documentation')}}">@lang('messages.documentation.link')</a>
                  </li>
                  @can('showFeedback')
                    <li>
                      <a href="{{route('teacher.feedback')}}">@lang('messages.feedback.link')</a>
                    </li>
                  @endcan
                </ul>
              </li>
            </ul>
          @endif

          <ul class="nav navbar-nav navbar-right">
            <!-- Authentication Links -->
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                {{ Auth::user()->name() }} <span class="caret"></span>
              </a>

              <ul class="dropdown-menu" role="menu">
                <li>
                  <a href="{{ route('logout') }}">@lang('auth.logout')</a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      @endif
    </div>
  </nav>

  @yield('content')
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
