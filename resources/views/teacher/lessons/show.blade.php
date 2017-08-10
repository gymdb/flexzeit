@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('lessons.show.heading', ['date' => $lesson->date, 'number' => $lesson->number])</h2>
    <teacher-lesson :id="{{$lesson->id}}" :attendance-checked="{{json_encode($attendanceChecked)}}" inline-template>
      <div class="panel-body">
        @if($lesson->cancelled)
          <p><strong>@lang('lessons.show.cancelled')</strong></p>
        @endif

        <dl class="dl-horizontal dl-narrow">
          @if($isAdmin)
            <dt>@lang('messages.teacher')</dt>
            <dd>{{$lesson->teacher->name()}}</dd>
          @endif
          @if($lesson->course)
            <dt>@lang('messages.course')</dt>
            <dd><a href="{{route('teacher.courses.show', $lesson->course->id)}}">{{$lesson->course->name}}</a></dd>
          @endif
          <dt>@lang('messages.room')</dt>
          <dd>{{$lesson->room->name}}</dd>
        </dl>

        @if($allowCancel)
          <confirm confirm-text="@lang('lessons.cancel.confirm')" inline-template>
            <form action="{{route('teacher.lessons.cancel', [$lesson->id])}}" method="post" @submit="destroy($event)">
              {{csrf_field()}}
              <p>
                <button class="btn btn-default">@lang('lessons.cancel.submit')</button>
              </p>
            </form>
          </confirm>
        @endif

        <h3>@lang('lessons.registrations.heading')</h3>
        @if($registrations->isEmpty())
          @if($showRegister)
            <p>
              <a href="#" class="btn btn-default" @click.prevent="openRegister">@lang('lessons.register.button')</a>
            </p>
          @endif
          <p>@lang('lessons.registrations.none')</p>
        @else
          <error :error="attendanceError">@lang('lessons.attendance.error')</error>
          <error :error="unregisterError">@lang('lessons.unregister.error')</error>

          @if($showAttendance || $showRegister)
            <p>
              @if($showRegister)
                <a href="#" class="btn btn-default" @click.prevent="openRegister">@lang('lessons.register.button')</a>
              @endif

              @if($attendanceChecked)
                <span class="glyphicon glyphicon-ok text-success"></span> @lang('lessons.attendance.checked')
              @elseif($attendanceChangeable)
                <span v-if="modifiedAttendanceChecked">
                        <span class="glyphicon glyphicon-ok text-success"></span> @lang('lessons.attendance.checked')
                      </span>
                <a v-else href="#" class="btn btn-default" @click.prevent="setAttendanceChecked">@lang('lessons.attendance.button')</a>
              @endif
            </p>
          @endif

          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead class="sr-only">
              <tr>
                <th>@lang('messages.student')</th>
                @if($showAttendance)
                  <th>@lang('lessons.attendance.heading')</th>
                @endif
                @if($showRegister)
                  <th>@lang('lessons.unregister.heading')</th>
                @endif
                @if($showFeedback)
                  <th></th>
                @endif
                @if($isAdmin)
                  <th></th>
                @endif
              </tr>
              </thead>
              @foreach($registrations as $reg)
                <tr>
                  <td class="popover-container">
                    <popover trigger="hover" placement="right">
                      <img slot="content" class="popover-image" src="{{url($reg->student->image ?: '/images/avatar.png')}}"/>
                      <span>{{$reg->student->name()}}{{$reg->student->formsString()}}</span>
                    </popover>
                  </td>
                  @if($showAttendance)
                    <td>
                      <teacher-attendance :id="{{$reg->id}}" :attendance="{{json_encode($reg->attendance)}}"
                                          :excused="{{json_encode($reg->student->absences->isNotEmpty())}}"
                                          :changeable="{{json_encode($attendanceChangeable)}}"
                                          v-on:success="setAttendanceSuccess" v-on:error="setAttendanceError">
                      </teacher-attendance>
                    </td>
                  @endif
                  @if($showRegister)
                    <td>
                      <unregister :id="{{$reg->id}}" base-url="teacher"
                                  confirm-text="@lang('lessons.unregister.confirm', ['student' => $reg->student->name()])"
                                  v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                      </unregister>
                    </td>
                  @endif
                  @if($showFeedback)
                    <td>
                      <a href="#" class="btn btn-xs btn-default" @click.prevent="openFeedback({{$reg->id}})">
                        @lang('lessons.feedback.button')
                      </a>
                    </td>
                  @endif
                  @if($isAdmin)
                    <td>
                      <a href="#" class="btn btn-xs btn-default"
                         @click.prevent='openChangeRegistration(@json(["id" => $reg->student->id, "name" => $reg->student->name()]), "{{$lesson->date->toDateString()}}", {{$lesson->number}})'>
                        @lang('lessons.register.change')
                      </a>
                    </td>
                  @endif
                </tr>
              @endforeach
            </table>
          </div>

          @if($showFeedback)
            <teacher-feedback ref="feedbackModal"></teacher-feedback>
          @endif

          @if($isAdmin)
            <teacher-register-student ref="changeRegistrationModal"></teacher-register-student>
          @endif
        @endif

        @if($showRegister)
          <teacher-register ref="registerModal"
                            :groups='@json($groups)'
                            date="{{$lesson->date->toDateString()}}"
                            :number="{{$lesson->number}}"
                            :admin="@json($isAdmin)"
                            :lesson="{{$lesson->id}}">
          </teacher-register>
        @endif
      </div>
    </teacher-lesson>
  </section>
@endsection
