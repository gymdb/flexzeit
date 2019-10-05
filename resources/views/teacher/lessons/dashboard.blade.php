@extends('layouts.app')
@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('lessons.dashboard.heading', ['date' => \App\Helpers\Date::today()])</h2>
    <div class="panel-body">
      @if($lessons->isEmpty())
        @lang('lessons.dashboard.none')
      @else
        <div class="table-responsive">
          <table class="table">
            <thead>
            <tr>
              <th>@lang('messages.time')</th>
              <th>@lang('messages.course')</th>
              <th>@lang('messages.room')</th>
              <th>@lang('messages.participants')</th>
              <th>@lang('messages.category')</th>
              <th></th>
            </tr>
            </thead>
            @foreach($lessons as $lesson)
            <tr @if($lesson->course && !$lesson->cancelled) class= "category{{$lesson->course->category}}" @endif @if ($lesson->course && $lesson->cancelled)class=" text-muted category{{$lesson->course->category}}" @endif @if (!$lesson->course && $lesson->canceld) class="text-muted"  @endif>
                <td>@lang('messages.format.range', $lesson->time)</td>
                <td class="course">
                  @if($lesson->course)
                    <a href="{{route('teacher.courses.show', $lesson->course->id)}}">{{$lesson->course->name}}</a>
                  @endif
                </td>
                <td class="room">{{$lesson->room->name}}</td>
                <td>
                  {{$lesson->participants}}{{!$lesson->course ? "/{$lesson->room->capacity}" : ($lesson->course->maxstudents ? "/{$lesson->course->maxstudents}" : '')}}
                </td><td>
                  @if($lesson->course)
                    @lang('messages.categories.'.$lesson->course->category)
                  @endif
                </td>
                <td>
                  @if($lesson->cancelled)
                    @lang('lessons.dashboard.cancelled')
                  @else
                    <a href="{{route('teacher.lessons.show', $lesson->id)}}" class="hidden-print">@lang('lessons.dashboard.attendance')</a>
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
