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
  <!--suppress JSUnresolvedFunction, JSUnresolvedVariable -->
  <script>
    window.Laravel = @json($global);
  </script>
</head>
<body>
<div id="app">
  <nav class="navbar navbar-default navbar-static-top">
    <div class="container">
      @if(Illuminate\Support\Facades\Auth::guest())
        <div class="navbar-header">
          <a class="navbar-brand" href="{{url('/')}}">
            <img src="{{asset('images/logo.jpg')}}"/>
            {{ config('app.name', 'Flexzeit') }}
          </a>
        </div>
      @else
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse" title="@lang('nav.toggle')">
            <span class="sr-only">@lang('nav.toggle')</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>

          <a class="navbar-brand"
             href="{{Illuminate\Support\Facades\Auth::user()->isStudent() ? route('student.dashboard') : route('teacher.dashboard')}}">
            <img src="{{asset('images/logo.jpg')}}" class="clearfix"/>
            {{ config('app.name', 'Flexzeit') }}
          </a>
        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
          @if(Illuminate\Support\Facades\Auth::user()->isTeacher())
            <ul class="nav navbar-nav">
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                  @lang('nav.lessons.heading') <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                  <li>
                    <a href="{{route('teacher.lessons.index')}}">@lang('nav.lessons.list')</a>
                    <a href="{{route('teacher.courses.index')}}">@lang('nav.courses.list')</a>
                    <a href="{{route('teacher.courses.create')}}">@lang('nav.courses.create')</a>
                    <a href="{{route('teacher.courses.obligatory.create')}}">@lang('nav.courses.obligatory.create')</a>
                  </li>
                </ul>
              </li>

              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                  @lang('nav.reports') <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu">
                  @can('showRegistrations', \App\Models\Student::class)
                    <li>
                      <a href="{{route('teacher.registrations.list')}}">@lang('nav.registrations.list')</a>
                    </li>
                    <li>
                      <a href="{{route('teacher.registrations.missing')}}">@lang('nav.registrations.missing')</a>
                    </li>
                    <li>
                      <a href="{{route('teacher.registrations.absent')}}">@lang('nav.registrations.absent')</a>
                    </li>
                  @endcan
                  <li>
                    <a href="{{route('teacher.documentation.list')}}">@lang('nav.documentation.list')</a>
                  </li>
                  <li>
                    <a href="{{route('teacher.documentation.missing')}}">@lang('nav.documentation.missing')</a>
                  </li>
                  @can('showFeedback', \App\Models\Student::class)
                    <li>
                      <a href="{{route('teacher.feedback')}}">@lang('nav.feedback.list')</a>
                    </li>
                  @endcan
                  @can('listObligatory', \App\Models\Course::class)
                    <li>
                      <a href="{{route('teacher.courses.obligatory.list')}}">@lang('nav.courses.obligatory.list')</a>
                    </li>
                  @endcan
                  @can('show', \App\Models\BugReport::class)
                    <li>
                      <a href="{{route('teacher.bugreports.list')}}">@lang('nav.bugreports.list')</a>
                    </li>
                  @endcan
                </ul>
              </li>
            </ul>
          @endif

          <ul class="nav navbar-nav navbar-right">
            <!-- Authentication Links -->
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                {{ Illuminate\Support\Facades\Auth::user()->name() }} <span class="caret"></span>
              </a>

              <ul class="dropdown-menu" role="menu">
                <li>
                  <a href="{{ route('logout') }}">@lang('auth.logout')</a>
                </li>
                <li>
                  <a href="#" @click.prevent="$refs.bugReportModal.open()">@lang('nav.bugreports.create')</a>
                </li>
              </ul>
            </li>
          </ul>

          <bug-report ref="bugReportModal"></bug-report>
        </div>
      @endif
    </div>
  </nav>

  <main class="container">
    <div class="row">
      <div class="col-md-10 col-md-offset-1 print-full-width" style="display:none" v-show="true">
        @yield('content')
      </div>
      <p v-if="false" class="lead text-center"><span class="glyphicon glyphicon-refresh spin"></span></p>
    </div>
  </main>
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
