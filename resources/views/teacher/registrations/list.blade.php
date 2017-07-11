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
          default-start-date='{{$defaultStartDate}}'
          default-end-date='{{$defaultEndDate}}'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          error-text="@lang('registrations.list.error')">
        <div slot="empty" class="alert alert-warning">@lang('registrations.list.none')</div>
        <template scope="props">
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
              <tr v-for="reg in props.data" :class="{'text-muted': reg.cancelled}">
                <td>@{{$d(moment(reg.date), 'short')}}</td>
                <td>@{{$t('messages.range', reg.time)}}</td>
                <td>@{{reg.teacher}}</td>
                <td><a v-if="reg.course" href="#">@{{reg.course.name}}</a></td>
                <template v-if="reg.cancelled">
                  <td>@lang('lessons.dashboard.cancelled')</td>
                  <td></td>
                </template>
                <template v-else>
                  <td>
                    <teacher-attendance v-if="!moment().isBefore(reg.date)" :id="reg.id" :attendance="reg.attendance"
                                :excused="reg.excused" :changeable="false"></teacher-attendance>
                  </td>
                </template>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
