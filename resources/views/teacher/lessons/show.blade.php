@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('lessons.lesson', ['date' => $lesson->date, 'start' => $lesson->start, 'end' => $lesson->end])</h2>
          <teacher-lesson :id="{{$lesson->id}}" :attendance-checked="{{json_encode($attendanceChecked)}}" inline-template>
            <div class="panel-body">
              @if($lesson->cancelled)
                <p><strong>@lang('lessons.cancelled.long')</strong></p>
              @endif

              <dl class="dl-horizontal dl-narrow">
                @if($lesson->course)
                  <dt>@lang('messages.course')</dt>
                  <dd><a href="{{route('teacher.courses.show', [$lesson->course->id])}}">{{$lesson->course->name}}</a></dd>
                  <dt>@lang('messages.room')</dt>
                  <dd>{{$lesson->course->room}}</dd>
                @else
                  <dt>@lang('messages.room')</dt>
                  <dd>{{$lesson->room}}</dd>
                @endif
              </dl>

              <h3>@lang('lessons.registrations.header')</h3>
              @if($registrations->isEmpty())
                <p>@lang('lessons.registrations.none')</p>
              @else
                <error :error="attendanceError">@lang('lessons.attendance.error')</error>
                <error :error="unregisterError">@lang('lessons.unregister.error')</error>

                @if($showAttendance)
                  <p>
                    @if($attendanceChangeable)
                      <span v-if="attendanceChecked">
                        <span class="glyphicon glyphicon-ok text-success"></span> @lang('lessons.attendance.checked')
                      </span>
                      <a v-else href="#" class="btn btn-default" @click.prevent="setAttendanceChecked">@lang('lessons.attendance.setChecked')</a>
                    @elseif($attendanceChecked)
                      <span class="glyphicon glyphicon-ok text-success"></span> @lang('lessons.attendance.checked')
                    @endif
                  </p>
                @endif

                <div class="table-responsive">
                  <table class="table table-squeezed">
                    <thead class="sr-only">
                    <tr>
                      <th>@lang('messages.student')</th>
                      @if($showAttendance)
                        <th>@lang('lessons.attendance.header')</th>
                      @endif
                      @if($showUnregister)
                        <th>@lang('lessons.unregister.header')</th>
                      @endif
                      @if($showFeedback)
                        <th></th>
                      @endif
                    </tr>
                    </thead>
                    @foreach($registrations as $reg)
                      <tr>
                        <td class="popover-container">
                          <popover trigger="hover" placement="right">
                            <img slot="content" class="student-image" src="{{url($reg->student->image ?: '/images/avatar.png')}}"/>
                            <span>
                                {{$reg->student->name()}}
                              @if($reg->student->forms->isNotEmpty())
                                ({{$reg->student->forms->implode('group.name', ', ')}})
                              @endif
                              </span>
                          </popover>
                        </td>
                        @if($showAttendance)
                          <td>
                            <attendance :id="{{$reg->id}}" :attendance="{{json_encode($reg->attendance)}}"
                                        :excused="{{json_encode($reg->student->absences->isNotEmpty())}}"
                                        :changeable="{{json_encode($attendanceChangeable)}}"
                                        present-text="@lang('lessons.attendance.present')"
                                        excused-text="@lang('lessons.attendance.excused')"
                                        absent-text="@lang('lessons.attendance.absent')"
                                        v-on:success="setAttendanceSuccess" v-on:error="setAttendanceError">
                            </attendance>
                          </td>
                        @endif
                        @if($showUnregister)
                          <td>
                            <unregister :id="{{$reg->id}}" base-url="teacher" button-text="@lang('lessons.unregister.button')"
                                        confirm-text="@lang('lessons.unregister.confirm', ['student' => $reg->student->name()])"
                                        v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                            </unregister>
                          </td>
                        @endif
                        @if($showFeedback)
                          <td>
                            <a href="#" class="btn btn-xs btn-default"
                               @click.prevent="openFeedback({{$reg->id}})">@lang('lessons.feedback.button')</a>
                          </td>
                        @endif
                      </tr>
                    @endforeach
                  </table>
                </div>

                @if($showFeedback)
                  <feedback-edit ref="feedbackModal"
                                 ok-text="@lang('lessons.feedback.save')" cancel-text="@lang('messages.cancel')"
                                 label="@lang('lessons.feedback.label')" header="@lang('lessons.feedback.header')"
                                 load-error-text="@lang('lessons.feedback.loadError')" save-error-text="@lang('lessons.feedback.saveError')">
                  </feedback-edit>
                @endif
              @endif
            </div>
          </teacher-lesson>
        </section>
      </div>
    </div>
  </main>
@endsection
