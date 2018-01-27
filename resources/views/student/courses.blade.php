@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('nav.courses.next')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('student.api.courses')}}"
          :teachers='@json($teachers)'
          default-start-date='{{$defaultStartDate->toDateString()}}'
          default-end-date='{{$defaultEndDate->toDateString()}}'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          error-text="@lang('courses.index.error')">
        <div slot="empty" class="alert alert-warning">@lang('courses.index.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.course')</th>
                <th>@lang('messages.participants')</th>
              </tr>
              </thead>
              <tr v-for="course in props.data">
                <td>
                  <a :href="'{{route('student.day', '')}}/' + course.first">
                    @{{$d(moment(course.first), 'short')}}
                  </a>
                  <span v-if="course.last">&ndash; @{{$d(moment(course.last), 'short')}}</span>
                </td>
                <td>@{{$t('messages.range', course.time)}}</td>
                <td class="course">
                  <popover trigger="hover" placement="right" :ref="'popover-' + key">
                    <div slot="content">
                      <p v-if="course.teacher.subjects">@{{course.teacher.subjects}}</p>
                      <p v-if="course.teacher.info">@{{course.teacher.info}}</p>
                      <p>
                        <img class="popover-image" src="{{url('/images/avatar.png')}}"
                             :src="course.teacher.image || '{{url('/images/avatar.png')}}'"
                             @load="$refs['popover-' + key][0].position()"/>
                      </p>
                    </div>
                    <span>@{{course.teacher.name}}</span>
                  </popover>
                </td>
                <td class="course">
                  <popover v-if="course.description" trigger="hover" placement="right">
                    <div slot="content"> @{{course.description}}</div>
                    <span>@{{course.name}}</span>
                  </popover>
                  <span v-else>@{{course.name}}</span>
                </td>
                <td>@{{course.students}}<span
                      v-if="course.maxstudents">/@{{course.maxstudents}}</span></td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection