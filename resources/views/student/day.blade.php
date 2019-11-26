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
            <table class="table">
              <thead>
              <tr>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.course')</th>
                <th>@lang('messages.room')</th>
                @if($allowRegistration)
                  <th class="hidden-print"></th>
                @endif
              </tr>
              </thead>
              @foreach($lessons as $lesson)
                <tr>
                  <td>@lang('messages.format.range', $lesson->time)</td>
                  @if($lesson->id)
                    <td>{{$lesson->teacher->name()}}</td>
                    <td class="course">{{$lesson->course ? $lesson->course->name : ''}}</td>
                    <td class="room">{{$lesson->room->name}}</td>
                    @if($allowRegistration)
                      <td class="hidden-print">
                        @if($lesson->unregisterPossible)
                          @if($lesson->course)
                            <unregister :id="{{$lesson->course->id}}" course base-url="student" :button="false"
                                        confirm-text="@lang('student.unregister.confirmCourse', ['course' => $lesson->course->name])"
                                        v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                              <span class="glyphicon glyphicon-remove-sign register-link"></span>
                              <span class="sr-only"> @lang('student.unregister.label')</span>
                            </unregister>
                          @else
                            <unregister :id="{{$lesson->registration_id}}" :course="false" base-url="student" :button="false"
                                        confirm-text="@lang('student.unregister.confirm', ['teacher' => $lesson->teacher->name()])"
                                        v-on:success="setUnregisterSuccess" v-on:error="setUnregisterError">
                              <span class="glyphicon glyphicon-remove-sign register-link"></span>
                              <span class="sr-only"> @lang('student.unregister.label')</span>
                            </unregister>
                          @endif
                        @endif
                      </td>
                    @endif
                  @elseif($lesson->isOffday)
                    <td colspan="3">@lang('student.offday')</td>
                    @if($allowRegistration)
                      <td></td>
                    @endif
                  @else
                    @php $hasMissing = true; @endphp
                    <td colspan="3" class="text-danger">@lang('student.missing')</td>
                    @if($allowRegistration)
                      <td></td>
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
    <section class="panel panel-default has-popovers">
      <h2 class="panel-heading">@lang('student.available.heading')<span class="pull-right">
        <popover trigger="hover" placement="right" :ref="'popover-legend'">
	  <div slot="content">
	    <table>
	      <tr class="category0"><td>Nawi</td></tr>
	      <tr class="category1"><td>Sprachen</td></tr>
	      <tr class="category2"><td>Kreativ</td></tr>
	      <tr class="category3"><td>Sport</td></tr>
	      <tr class="category4"><td>Gewi</td></tr>
	      <tr class="category5"><td>Anderes</td></tr>
	    </table>
	  </div>
	  <span> Legende </span>
	  </popover>
        </span></h2>
      <div class="panel-body">
        <filtered-list
            url="{{route('student.api.available', $date->toDateString())}}"
            :teachers='@json($teachers)'
            :subjects='@json($subjects)'
            :room-types='@json($roomTypes)'
            error-text="@lang('student.available.error')">
          <div slot="empty" class="alert alert-warning">@lang('student.available.none')</div>
          <template scope="props">
            <div class="table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.teacher')</th>
                  <th>@lang('messages.course')</th>
                  <th>@lang('messages.room')</th>
                  @if($allowRegistration)
                    <th class="hidden-print"></th>
                  @endif
                </tr>
                </thead>
                <tr v-for="(lesson, key) in props.data" :class="typeof(lesson.course) !== 'undefined' ? 'category'+{{'lesson.course.category'}} : ''">
                  <td>@{{$t('messages.range', lesson.time)}}</td>
                  <td>
                    <popover trigger="hover" placement="right" :ref="'popover-' + key">
                      <div slot="content">
                        <p v-if="lesson.teacher.subjects">@{{lesson.teacher.subjects}}</p>
                        <p v-if="lesson.teacher.info">@{{lesson.teacher.info}}</p>
                        <p>
                          <img class="popover-image" src="{{url('/images/avatar.png')}}"
                               :src="lesson.teacher.image || '{{url('/images/avatar.png')}}'"
                               @load="$refs['popover-' + key][0].position()"/>
                        </p>
                      </div>
                      <span>@{{lesson.teacher.name}}</span>
                    </popover>
                  </td>
                  <td class="course">@{{lesson.course ? lesson.course.name : ''}}</td>
                  <td class="room">@{{lesson.room}}</td>
                  @if ($allowRegistration)
                    <td class="hidden-print">
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
