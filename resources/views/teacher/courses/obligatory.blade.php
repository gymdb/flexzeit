@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('courses.obligatory.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url={{route('teacher.api.courses.obligatory')}}
          :groups='@json($groups)'
          :teachers='@json($teachers)'
          :subjects='@json($subjects)'
          default-start-date='{{$defaultStartDate}}'
          default-end-date='{{$defaultEndDate}}'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          :require-group="false"
          error-text="@lang('courses.obligatory.error')">
        <div slot="empty" class="alert alert-warning">@lang('courses.obligatory.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.course')</th>
                <th>@lang('messages.group')</th>
                <th></th>
              </tr>
              </thead>
              <tr v-for="course in props.data">
                <td v-if="course.last">@{{$t('messages.range', {start: $d(moment(course.first), 'short'), end: $d(moment(course.last), 'short')})}}</td>
                <td v-else>@{{$d(moment(course.first), 'short')}}</td>
                <td>@{{$t('messages.range', course.time)}}</td>
                <td>@{{course.teacher}}</td>
                <td>@{{course.name}}</td>
                <td>@{{course.groups.join(', ')}}</td>
                <td>
                  <a :href="'{{route('teacher.courses.show', '')}}/' + course.id">@lang('courses.index.details')</a>
                </td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
