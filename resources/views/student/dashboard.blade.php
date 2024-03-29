@extends('layouts.app')

@section('content')
  <student-registrations inline-template>
    <div>
      <section class="panel panel-default">
        <h2 class="panel-heading">
          @lang('student.today.heading', ['date' => \App\Helpers\Date::today()])
          <a href="javascript:print()" class="pull-right hidden-print"><span class="glyphicon glyphicon-print"></span></a>
        </h2>
        <div class="panel-body">
          @if($today->isEmpty())
            @lang('student.today.none')
          @else
            <div class="table-responsive">
              <table class="table">
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
                    @if($lesson->id)
                      <td>{{$lesson->teacher->name()}}</td>
                      <td class="course">{{$lesson->course ? $lesson->course->name : ''}}
                       @if($lesson->course)
                          <span class="glyphicon glyphicon-info-sign" title="{{$lesson->course->description}}"></span>
                       @endif
                      </td>
                      <td class="room">{{$lesson->room->name}}</td>
                    @elseif($lesson->isOffday)
                      <td colspan="3">@lang('student.offday')</td>
                    @else
                      <td colspan="3" class="text-danger">@lang('student.missing')</td>
                       @if(date('Hi')<725)
                         <td>
                         <a href="{{route('student.day', $lesson->date->toDateString())}}" title="@lang('student.register.label')"
                            class="hidden-print">
                          <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                          <span class="sr-only">@lang('student.register.label')</span>
                          </a>
                         </td>
                       @endif
                    @endif
                  </tr>
                @endforeach
              </table>
            </div>
          @endif
        </div>
      </section>

      <section class="panel panel-default print-last">
        <h2 class="panel-heading">@lang('student.upcoming.heading')</h2>
        <div class="panel-body">
          <error :error="error">@lang('student.unregister.error')</error>

          @if($upcoming->isEmpty())
            @lang('student.upcoming.none')
          @else
            <div class="table-responsive">
              <table class="table">
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
                    <td @if($prevDate == $lesson->date) class="hidden-date" @endif><span>{{$lesson->date}}</span></td>
                    <td>@lang('messages.format.range', $lesson->time)</td>
                    @if($lesson->id)
                      <td>{{$lesson->teacher->name()}}</td>
                      <td class="course">{{$lesson->course ? $lesson->course->name : ''}}
                       @if($lesson->course)
                         <span class="glyphicon glyphicon-info-sign" title="{{$lesson->course->description}}"></span>
                       @endif
                      </td>
                      <td class="room">{{$lesson->room->name}}</td>
                      <td>
                        @if($lesson->unregisterPossible)
                          @if($lesson->course)
                            <unregister :id="{{$lesson->course->id}}" course base-url="student" :button="false"
                                        confirm-text="@lang('student.unregister.confirmCourse', ['course' => $lesson->course->name])"
                                        v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                              <span class="glyphicon glyphicon-remove-sign register-link hidden-print"></span>
                              <span class="sr-only"> @lang('student.unregister.label')</span>
                            </unregister>
                          @else
                            <unregister :id="{{$lesson->registration_id}}" :course="false" base-url="student" :button="false"
                                        confirm-text="@lang('student.unregister.confirm', ['teacher' => $lesson->teacher->name()])"
                                        v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                              <span class="glyphicon glyphicon-remove-sign register-link hidden-print"></span>
                              <span class="sr-only"> @lang('student.unregister.label')</span>
                            </unregister>
                          @endif
                        @endif
                      </td>
                    @elseif($lesson->isOffday)
                      <td colspan="3">@lang('student.offday')</td>
                      <td></td>
                    @else
                      <td colspan="3" class="text-danger">@lang('student.missing')</td>
                      <td>
                        <a href="{{route('student.day', $lesson->date->toDateString())}}" title="@lang('student.register.label')"
                           class="hidden-print">
                          <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                          <span class="sr-only">@lang('student.register.label')</span>
                        </a>
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

      <section class="panel panel-default hidden-print">
        <h2 class="panel-heading">@lang('student.documentation.heading')</h2>
        <div class="panel-body">
          @if($documentation->isEmpty())
            @lang('student.documentation.none')
          @else
            <div class="table-responsive">
              <table class="table">
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
                    <td @if($prevDate == $reg->lesson->date) class="hidden-date" @endif><span>{{$reg->lesson->date}}</span></td>
                    <td>@lang('messages.format.range', $reg->lesson->time)</td>
                    <td>{{$reg->lesson->teacher->name()}}</td>
                    <td class="course">{{$reg->lesson->course ? $reg->lesson->course->name : ''}}</td>
                    <td>
                      <a href="#" class="btn btn-xs {{$reg->documentation ? 'btn-default' : 'btn-danger'}}"
                         @click.prevent='openDocumentation({{$reg->id}}, @json($reg->lesson->teacher->name()), $event.target)'>
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
