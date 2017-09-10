@extends('layouts.app')

@section('content')
  <section class="panel panel-default popover-panel">
    <h2 class="panel-heading">@lang('courses.show.heading', ['name' => $course->name])</h2>
    <div class="panel-body">
      <dl class="dl-horizontal">
        <dt>@lang('messages.teacher')</dt>
        <dd>{{$firstLesson->teacher->name()}}</dd>
        <dt>@lang('courses.data.room')</dt>
        <dd>{{$firstLesson->room->name}}</dd>
        <dt>@lang('courses.data.description')</dt>
        <dd>{{$course->description}}</dd>
        @if($course->yearfrom || $course->yearto)
          <dt>@lang('courses.data.year.title')</dt>
          <dd>
            @if($course->yearfrom && $course->yearto)
              @if($course->yearfrom === $course->yearto)
                @lang('courses.data.year.one', ['year' => $course->yearfrom])
              @else
                @lang('courses.data.year.range', ['from' => $course->yearfrom, 'to' => $course->yearto])
              @endif
            @elseif($course->yearfrom)
              @lang('courses.data.year.higher', ['year' => $course->yearfrom])
            @else
              @lang('courses.data.year.lower', ['year' => $course->yearto])
            @endif
          </dd>
        @endif
        @if($course->maxstudents)
          <dt>@lang('courses.data.maxStudents')</dt>
          <dd>{{$course->maxstudents}}</dd>
        @endif
      </dl>

      @if($allowDestroy)
        <confirm confirm-text="@lang('courses.destroy.confirm')" inline-template>
          <form action="{{route('teacher.courses.destroy', [$course->id])}}" method="post" @submit="destroy($event)">
            {{csrf_field()}}
            {{method_field('delete')}}
            <p>
              <a href="{{route('teacher.courses.edit', [$course->id])}}" class="btn btn-default">@lang('courses.edit.link')</a>
              <button class="btn btn-default">@lang('courses.destroy.submit')</button>
            </p>
          </form>
        </confirm>
      @elseif($isOwner)
        <p><a href="{{route('teacher.courses.edit', [$course->id])}}" class="btn btn-default">@lang('courses.edit.link')</a></p>
      @endif

      @if($showRegister)
        <p>
          <a href="#" class="btn btn-default"
             @click.prevent="$refs.registerModal.open()">@lang($firstLesson->date->isPast() ? 'lessons.register.buttonPast' : 'lessons.register.button')</a>
        </p>
      @endif

      <h3>@lang('courses.show.lessons')</h3>
      <ul>
        @foreach($lessons as $lesson)
          <li @if($lesson->cancelled)class="text-muted"@endif>
            @if($isOwner)
              <a href="{{route('teacher.lessons.show', $lesson->id)}}">
                <strong>{{$lesson->date}}</strong>, @lang('messages.format.range', $lesson->time)
              </a>
            @else
              <strong>{{$lesson->date}}</strong>, @lang('messages.format.range', $lesson->time)
            @endif
          </li>
        @endforeach
      </ul>

      <h3>@lang('courses.registrations.heading')</h3>
      @if($registrations->isEmpty())
        <p>@lang('courses.registrations.none')</p>
      @else
        <ul>
          @foreach($registrations as $reg)
            <li class="popover-container">
              <popover trigger="hover" placement="right">
                <img slot="content" class="popover-image" src="{{url($reg->student->image ?: '/images/avatar.png')}}"/>
                <span>{{$reg->student->name()}}{{$reg->student->formsString()}}</span>
              </popover>
            </li>
          @endforeach
        </ul>
      @endif

      @if($showRegister)
        <teacher-register ref="registerModal" course
                          :groups='@json($groups)'
                          :admin="@json($isAdmin)"
                          :id="{{$course->id}}">
        </teacher-register>
      @endif
    </div>
  </section>
@endsection
