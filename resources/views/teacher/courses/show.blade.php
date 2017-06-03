@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('courses.show', ['name' => $course->name])</h2>
          <div class="panel-body">
            <dl class="dl-horizontal">
              <dt>@lang('messages.room')</dt>
              <dd>{{$course->room}}</dd>
              <dt>@lang('courses.description')</dt>
              <dd>{{$course->description}}</dd>
              @if($course->yearfrom || $course->yearto)
                <dt>@lang('courses.year.title')</dt>
                <dd>
                  @if($course->yearfrom && $course->yearto)
                    @if($course->yearfrom === $course->yearto)
                      @lang('courses.year.one', ['year' => $course->yearfrom])
                    @else
                      @lang('courses.year.range', ['from' => $course->yearfrom, 'to' => $course->yearto])
                    @endif
                  @elseif($course->yearfrom)
                    @lang('courses.year.higher', ['year' => $course->yearfrom])
                  @else
                    @lang('courses.year.lower', ['year' => $course->yearto])
                  @endif
                </dd>
              @endif
              @if($course->maxstudents)
                <dt>@lang('courses.maxStudents')</dt>
                <dd>{{$course->maxstudents}}</dd>
              @endif
            </dl>

            <h3>@lang('courses.lessons')</h3>
            <ul>
              @foreach($lessons as $lesson)
                <li @if($lesson->cancelled)class="text-muted"@endif>
                  <a href="{{route('teacher.lessons.show', $lesson->id)}}"><strong>{{$lesson->date}}</strong>,
                    {{$lesson->start}} &ndash; {{$lesson->end}}</a>
                </li>
              @endforeach
            </ul>

            <h3>@lang('lessons.registrations.header')</h3>
            @if($registrations->isEmpty())
              <p>@lang('lessons.registrations.none')</p>
            @else
              <ul>
                @foreach($registrations as $reg)
                  <li>
                    <popover trigger="hover" placement="right">
                      <img slot="content" class="student-image" src="{{url($reg->student->image ?: '/images/avatar.png')}}"/>
                      <span>
                        {{$reg->student->name()}}
                        @if($reg->student->forms->isNotEmpty())
                          ({{$reg->student->forms->implode('group.name', ', ')}})
                        @endif
                      </span>
                    </popover>
                  </li>
                @endforeach
              </ul>
            @endif
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
