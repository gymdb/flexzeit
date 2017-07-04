@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('lessons.dashboard.heading', ['date' => \App\Helpers\Date::today()])</h2>
    <div class="panel-body">
      @if($lessons->isEmpty())
        @lang('lessons.dashboard.none')
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
                <td>@lang('messages.format.range', $lesson->time)</td>
                @if($lesson->course)
                  <td><a href="{{route('teacher.courses.show', $lesson->course->id)}}">{{$lesson->course->name}}</a></td>
                  <td>{{$lesson->course->room}}</td>
                @else
                  <td></td>
                  <td>{{$lesson->room}}</td>
                @endif
                <td>
                  @if($lesson->cancelled)
                    @lang('lessons.dashboard.cancelled')
                  @else
                    <a href="{{route('teacher.lessons.show', $lesson->id)}}">@lang('lessons.dashboard.attendance')</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </table>
        </div>
      @endif
    </div>
  </section>
@endsection
