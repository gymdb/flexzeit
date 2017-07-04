@extends('layouts.app')

@section('content')
  <student-registrations inline-template>
    <div>
      <section class="panel panel-default">
        <h2 class="panel-heading">@lang('student.today.heading', ['date' => \App\Helpers\Date::today()])</h2>
        <div class="panel-body">
          @if(empty($today))
            @lang('student.today.none')
          @else
            <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.teacher')</th>
                  <th>@lang('messages.course')</th>
                  <th>@lang('messages.room')</th>
                </tr>
                </thead>
                @foreach($today as $lesson)
                  <tr>
                    <td>@lang('messages.format.range', $lesson->time)</td>
                    @if(!$lesson->id)
                      <td colspan="3" class="text-danger">@lang('student.missing')</td>
                    @elseif($lesson->course)
                      <td>{{$lesson->teacher->name()}}</td>
                      <td>{{$lesson->course->name}}</td>
                      <td>{{$lesson->course->room}}</td>
                    @else
                      <td>{{$lesson->teacher->name()}}</td>
                      <td></td>
                      <td>{{$lesson->room}}</td>
                    @endif
                  </tr>
                @endforeach
              </table>
            </div>
          @endif
        </div>
      </section>

      <section class="panel panel-default">
        <h2 class="panel-heading">@lang('student.upcoming.heading')</h2>
        <div class="panel-body">
          <error :error="error">@lang('student.unregister.error')</error>

          @if(empty($upcoming))
            @lang('student.upcoming.none')
          @else
            <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.date')</th>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.teacher')</th>
                  <th>@lang('messages.course')</th>
                  <th>@lang('messages.room')</th>
                  <th></th>
                </tr>
                </thead>
                @php $prevDate = null @endphp
                @foreach($upcoming as $lesson)
                  <tr>
                    <td @if($prevDate == $lesson->date) class="invisible" @endif>{{$lesson->date}}</td>
                    <td>@lang('messages.format.range', $lesson->time)</td>
                    @if(!$lesson->id)
                      <td colspan="3" class="text-danger">@lang('student.missing')</td>
                      <td>
                        <a href="{{route('student.day', $lesson->date->toDateString())}}" title="@lang('student.register.label')">
                          <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                          <span class="sr-only">@lang('student.register.label')</span>
                        </a>
                      </td>
                    @elseif($lesson->course)
                      <td>{{$lesson->teacher->name()}}</td>
                      <td>{{$lesson->course->name}}</td>
                      <td>{{$lesson->course->room}}</td>
                      <td>
                        @if($lesson->course->firstLesson()->date >= $firstRegisterDate && !$lesson->obligatory)
                          <unregister :id="{{$lesson->course->id}}" :course="true" base-url="student" :button="false"
                                      confirm-text="@lang('student.unregister.confirmCourse', ['course' => $lesson->course->name])"
                                      v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                            <span class="glyphicon glyphicon-remove-sign register-link"></span>
                            <span class="sr-only"> @lang('student.unregister.label')</span>
                          </unregister>
                        @endif
                      </td>
                    @else
                      <td>{{$lesson->teacher->name()}}</td>
                      <td></td>
                      <td>{{$lesson->room}}</td>
                      <td>
                        @if($lesson->date >= $firstRegisterDate)
                          <unregister :id="{{$lesson->registration_id}}" :course="false" base-url="student" :button="false"
                                      confirm-text="@lang('student.unregister.confirm', ['teacher' => $lesson->teacher->name()])"
                                      v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                            <span class="glyphicon glyphicon-remove-sign register-link"></span>
                            <span class="sr-only"> @lang('student.unregister.label')</span>
                          </unregister>
                        @endif
                      </td>
                    @endif
                  </tr>
                  @php $prevDate = $lesson->date @endphp
                @endforeach
              </table>
            </div>
          @endif
        </div>
      </section>

      <section class="panel panel-default">
        <h2 class="panel-heading">@lang('student.documentation.heading')</h2>
        <div class="panel-body">
          @if(empty($documentation))
            @lang('student.documentation.none')
          @else
            <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.date')</th>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.teacher')</th>
                  <th>@lang('messages.course')</th>
                  <th></th>
                </tr>
                </thead>
                @php $prevDate = null @endphp
                @foreach($documentation as $reg)
                  <tr>
                    <td @if($prevDate == $reg->lesson->date) class="invisible" @endif>{{$reg->lesson->date}}</td>
                    <td>@lang('messages.format.range', $reg->lesson->time)</td>
                    @if($reg->lesson->course)
                      <td>{{$reg->lesson->teacher->name()}}</td>
                      <td>{{$reg->lesson->course->name}}</td>
                    @else
                      <td>{{$reg->lesson->teacher->name()}}</td>
                      <td></td>
                    @endif
                    <td>
                      <a href="#" class="btn btn-xs {{$reg->documentation ? 'btn-default' : 'btn-danger'}}" @click.prevent="openDocumentation({{$reg->id}})">
                        @lang($reg->documentation ? 'student.documentation.edit' : 'student.documentation.add')
                      </a>
                    </td>
                  </tr>
                  @php $prevDate = $reg->lesson->date @endphp
                @endforeach
              </table>
            </div>
          @endif
        </div>
      </section>

      <student-documentation ref="documentationModal"></student-documentation>
    </div>
  </student-registrations>
@endsection
