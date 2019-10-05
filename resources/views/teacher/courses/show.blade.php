@extends('layouts.app')

@section('content')
  <section class="panel panel-default has-popovers">
    <h2 class="panel-heading">@lang('courses.show.heading', ['name' => $course->name])</h2>
    <course-show inline-template>
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
          <dt>@lang('courses.data.category')</dt>
          <dd>@{{categoryOptionsList[{!! json_encode($course->category) !!}].label}}</dd>
        </dl>

        @if($allowDestroy)
          <confirm confirm-text="@lang('courses.destroy.confirm')" inline-template>
            <form action="{{route('teacher.courses.destroy', [$course->id])}}" method="post" @submit="destroy($event)" class="hidden-print">
              {{csrf_field()}}
              {{method_field('delete')}}
              <p>
                <a href="{{route('teacher.courses.edit', [$course->id])}}" class="btn btn-default">@lang('courses.edit.link')</a>
                <button class="btn btn-default">@lang('courses.destroy.submit')</button>
              </p>
            </form>
          </confirm>
        @elseif($isOwner)
          <p class="hidden-print"><a href="{{route('teacher.courses.edit', [$course->id])}}" class="btn btn-default">@lang('courses.edit.link')</a>
          </p>
        @endif

        @if($showRegister)
          <p class="hidden-print">
            <a href="#" class="btn btn-default" @click.prevent="openRegister">
              @lang($firstLesson->date->isPast() ? 'lessons.register.buttonPast' : 'lessons.register.button')
            </a>
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
          @if($showRegister)
            <error :error="unregisterError">@lang('courses.unregister.error')</error>
          @endif

          <div class="table-responsive">
            <table class="table table-squeezed table-condensed">
              <thead class="sr-only">
              <tr>
                <th>@lang('messages.student')</th>
                @if($showRegister)
                  <th class="hidden-print">@lang('courses.unregister.heading')</th>
                @endif
              </tr>
              </thead>
              @foreach($registrations as $reg)
                <tr>
                  <td>
                    <popover trigger="hover" placement="right" ref="popover-{{$reg->student->id}}">
                      <img slot="content" class="popover-image" src="{{url($reg->student->image ?: '/images/avatar.png')}}"
                           @load="resizePopover({{$reg->student->id}})"/>
                      <span>{{$reg->student->name()}}{{$reg->student->formsString()}}</span>
                    </popover>
                  </td>
                  @if($showRegister)
                    <td class="hidden-print">
                      <unregister :id="{{$reg->student->id}}" base-url="teacher" course :course-id="{{$course->id}}"
                                  confirm-text="@lang('courses.unregister.confirm', ['student' => $reg->student->name()])"
                                  v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                      </unregister>
                    </td>
                  @endif
                </tr>
              @endforeach
            </table>
          </div>
        @endif

        @if($showRegister)
          <teacher-register ref="registerModal" course
                            :groups='@json($groups)'
                            :admin="@json($isAdmin)"
                            :id="{{$course->id}}">
          </teacher-register>
        @endif
      </div>
    </course-show>
  </section>
@endsection
