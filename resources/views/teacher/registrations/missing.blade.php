@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('registrations.missing.heading')</h2>
    <div class="panel-body">
      <teacher-absent inline-template>
        <filtered-list
            url="/teacher/api/registrations/missing"
            :groups='@json($groups)'
            min-date='{{$minDate->toDateString()}}'
            max-date='{{$maxDate->toDateString()}}'
            :disabled-days-of-week='@json($disabledDaysOfWeek)'
            :disabled-dates='@json($offdays)'
            :require-student="false"
            error-text="@lang('registrations.missing.error')">
          <div slot="empty" class="alert alert-warning">@lang('registrations.missing.none')</div>
          <template scope="props">
            <error :error="refreshError">@lang('lessons.untis.error')</error>

            <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.date')</th>
                  <th>@lang('messages.time')</th>
                  <th>@lang('messages.student')</th>
                  <th>@lang('registrations.absent.untis')</th>
                </tr>
                </thead>
                <tr v-for="item in props.data">
                  <td>@{{$d(moment(item.date), 'short')}}</td>
                  <td>@{{$t('messages.range', item.time)}}</td>
                  <td>@{{item.name}}</td>
                  <td>
                    <teacher-excused :date="item.date" :excused="false"
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
