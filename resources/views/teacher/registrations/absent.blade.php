@extends('layouts.app')

@section('content')
  <section class="panel panel-default has-dropdowns">
    <h2 class="panel-heading">@lang('registrations.absent.heading')</h2>
    <div class="panel-body">
      <teacher-absent inline-template>
        <filtered-list
            url="{{route('teacher.api.registrations.absent')}}"
            :groups='@json($groups)'
            min-date='{{$minDate->toDateString()}}'
            max-date='{{$maxDate->toDateString()}}'
            :disabled-days-of-week='@json($disabledDaysOfWeek)'
            :disabled-dates='@json($offdays)'
            :require-student="false"
            error-text="@lang('registrations.absent.error')">
          <div slot="empty" class="alert alert-warning">@lang('registrations.absent.none')</div>
          <template scope="props">
            <error :error="attendanceError">@lang('lessons.attendance.error')</error>
            <error :error="refreshError">@lang('registrations.absent.untisError')</error>

            <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.date')</th>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.teacher')</th>
                  <th>@lang('messages.student')</th>
                  <th>@lang('registrations.absent.attendance')</th>
                  <th>@lang('registrations.absent.untis')</th>
                </tr>
                </thead>
                <tr v-for="reg in props.data">
                  <td>@{{$d(moment(reg.date), 'short')}}</td>
                  <td>@{{$t('messages.range', reg.time)}}</td>
                  <td>@{{reg.teacher}}</td>
                  <td>@{{reg.name}}</td>
                  <td>
                    <teacher-attendance v-if="!moment().isBefore(reg.date)" :id="reg.id"
                                :attendance="reg.attendance" :excused="reg.excused" changeable
                                v-on:success="setAttendanceSuccess" v-on:error="setAttendanceError"></teacher-attendance>
                  </td>
                  <td>
                    <teacher-excused :date="reg.date" :excused="reg.excused"
                                     v-on:refreshed="setRefreshSuccess" v-on:error="setRefreshError"></teacher-excused>
                  </td>
                </tr>
              </table>
            </div>
          </template>
        </filtered-list>
      </teacher-absent>
    </div>
  </section>
@endsection
