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
              <th>@lang('messages.participants')</th>
              <th></th>
            </tr>
            </thead>
            @foreach($lessons as $lesson)
              <tr @if($lesson->cancelled)class="text-muted"@endif>
                <td>@lang('messages.format.range', $lesson->time)</td>
                <td>
                  @if($lesson->course)
                    <a href="{{route('teacher.courses.show', $lesson->course->id)}}">{{$lesson->course->name}}</a>
                  @endif
                </td>
                <td>{{$lesson->room->name}}</td>
                <td>
                  {{$lesson->participants}}{{!$lesson->course ? "/{$lesson->room->capacity}" : ($lesson->course->maxstudents ? "/{$lesson->course->maxstudents}" : '')}}
                </td>
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
