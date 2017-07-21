@extends('layouts.app')

@section('content')
  @php $hasMissing = false; @endphp
  <student-registrations inline-template>
    <section class="panel panel-default">
      <h2 class="panel-heading">@lang('student.day.heading', ['date' => $date])</h2>
      <div class="panel-body">
        @if($lessons->isEmpty())
          @lang('student.day.none')
        @else
          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead>
              <tr>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.course')</th>
                <th>@lang('messages.room')</th>
                @if($allowRegistration)
                  <th></th>
                @endif
              </tr>
              </thead>
              @foreach($lessons as $lesson)
                <tr>
                  <td>@lang('messages.format.range', $lesson->time)</td>
                  @if(!$lesson->id)
                    @php $hasMissing = true; @endphp
                    <td colspan="3" class="text-danger">@lang('student.missing')</td>
                    @if($allowRegistration)
                      <td></td>
                    @endif
                  @elseif($lesson->course)
                    <td>{{$lesson->teacher->name()}}</td>
                    <td>{{$lesson->course->name}}</td>
                    <td>{{$lesson->course->room}}</td>
                    @if($allowRegistration)
                      <td>
                        @if($lesson->unregisterPossible)
                          <unregister :id="{{$lesson->course->id}}" course base-url="student" :button="false"
                                      confirm-text="@lang('student.unregister.confirmCourse', ['course' => $lesson->course->name])"
                                      v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                            <span class="glyphicon glyphicon-remove-sign register-link"></span>
                            <span class="sr-only"> @lang('student.unregister.label')</span>
                          </unregister>
                        @endif
                      </td>
                    @endif
                  @else
                    <td>{{$lesson->teacher->name()}}</td>
                    <td></td>
                    <td>{{$lesson->room}}</td>
                    @if($allowRegistration)
                      <td>
                        @if($lesson->unregisterPossible)
                          <unregister :id="{{$lesson->registration_id}}" :course="false" base-url="student" :button="false"
                                      confirm-text="@lang('student.unregister.confirm', ['teacher' => $lesson->teacher->name()])"
                                      v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                            <span class="glyphicon glyphicon-remove-sign register-link"></span>
                            <span class="sr-only"> @lang('student.unregister.label')</span>
                          </unregister>
                        @endif
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
  </student-registrations>

  @if($hasMissing)
    <section class="panel panel-default">
      <h2 class="panel-heading">@lang('student.available.heading')</h2>
      <div class="panel-body">
        <filtered-list
            url="{{route('student.api.available', $date->toDateString())}}"
            :teachers='@json($teachers)'
            :subjects='@json($subjects)'
            error-text="@lang('student.available.error')">
          <div slot="empty" class="alert alert-warning">@lang('student.available.none')</div>
          <template scope="props">
            <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.teacher')</th>
                  <th>@lang('messages.course')</th>
                  <th>@lang('messages.room')</th>
                  @if($allowRegistration)
                    <th></th>
                  @endif
                </tr>
                </thead>
                <tr v-for="lesson in props.data">
                  <td>@{{$t('messages.range', lesson.time)}}</td>
                  <td class="popover-container">
                    <popover trigger="hover" placement="right">
                      <div slot="content">
                        <p v-if="lesson.teacher.subjects">@{{lesson.teacher.subjects}}</p>
                        <p v-if="lesson.teacher.info">@{{lesson.teacher.info}}</p>
                        <p>
                          <img class="popover-image" src="{{url('/images/avatar.png')}}"
                               :src="lesson.teacher.image || '{{url('/images/avatar.png')}}'"/>
                        </p>
                      </div>
                      <span>@{{lesson.teacher.name}}</span>
                    </popover>
                  </td>
                  <td><span v-if="lesson.course">@{{lesson.course.name}}</span></td>
                  <td>@{{lesson.room}}</td>
                  @if ($allowRegistration)
                    <td>
                      <a href="#" @click.prevent="$refs.registerModal.open(lesson)" title="@lang('student.register.button')">
                        <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                        <span class="sr-only">@lang('student.register.button')</span>
                      </a>
                    </td>
                  @endif
                </tr>
              </table>
            </div>
          </template>
        </filtered-list>
      </div>
    </section>

    @if($allowRegistration && $hasMissing)
      <student-register ref="registerModal"></student-register>
    @endif
  @endif
@endsection
