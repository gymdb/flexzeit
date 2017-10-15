@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('registrations.list.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('teacher.api.registrations')}}"
          :groups='@json($groups)'
          :teachers='@json($teachers)'
          :subjects='@json($subjects)'
          default-start-date='{{$defaultStartDate->toDateString()}}'
          default-end-date='{{$defaultEndDate->toDateString()}}'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          :require-student="false"
          error-text="@lang('registrations.list.error')">
        <div slot="empty" class="alert alert-warning">@lang('registrations.list.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.course')</th>
                <th>@lang('messages.room')</th>
                <th>@lang('messages.student')</th>
                <th></th>
              </tr>
              </thead>
              <tr v-for="reg in props.data" :class="{'text-muted': reg.cancelled}">
                <td>@{{$d(moment(reg.date), 'short')}}</td>
                <td>@{{$t('messages.range', reg.time)}}</td>
                <td>@{{reg.teacher}}</td>
                <td class="course"><a v-if="reg.course" :href="'{{route('teacher.courses.show', '')}}/' + reg.course.id">@{{reg.course.name}}</a></td>
                <td class="room">@{{reg.room}}</td>
                <td>@{{reg.student}}</td>
                <td v-if="reg.cancelled">@lang('lessons.dashboard.cancelled')</td>
                <td v-else>
                  <teacher-attendance v-if="!moment().isBefore(reg.date)" :id="reg.id" :attendance="reg.attendance"
                                      :excused="reg.excused" :changeable="false"></teacher-attendance>
                </td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
