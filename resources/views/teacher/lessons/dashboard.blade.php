@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('messages.today', ['date' => \App\Helpers\Date::today()])</h2>
          <div class="panel-body">
            @if($lessons->isEmpty())
              @lang('lessons.none')
            @else
              <div class="table-responsive">
                <table class="table table-squeezed">
                  <thead>
                  <tr>
                    <th>@lang('messages.time')</th>
                    <th>@lang('messages.course')</th>
                    <th>@lang('messages.room')</th>
                    <th></th>
                  </tr>
                  </thead>
                  @foreach($lessons as $lesson)
                    <tr @if($lesson->cancelled)class="text-muted"@endif>
                      <td>{{$lesson->start}} &ndash; {{$lesson->end}}</td>
                      @if($lesson->course)
                        <td>{{$lesson->course->name}}</td>
                        <td>{{$lesson->course->room}}</td>
                      @else
                        <td></td>
                        <td>{{$lesson->room}}</td>
                      @endif
                      <td>
                        @if($lesson->cancelled)
                          @lang('lessons.cancelled.short')
                        @else
                          <a href="{{route('teacher.lessons.show', $lesson->id)}}">@lang('lessons.attendance.check')</a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </table>
              </div>
            @endif
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
