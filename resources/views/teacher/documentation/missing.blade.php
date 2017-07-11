@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('registrations.documentation.missing.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('teacher.api.documentation.missing')}}"
          :groups='@json($groups)'
          :teachers='@json($teachers)'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          :require-student="false"
          error-text="@lang('registrations.documentation.missing.error')">
        <div slot="empty" class="alert alert-warning">@lang('registrations.documentation.missing.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.student')</th>
              </tr>
              </thead>
              <tr v-for="reg in props.data">
                <td>@{{$d(moment(reg.date), 'short')}}</td>
                <td>@{{$t('messages.range', reg.time)}}</td>
                <td>@{{reg.teacher}}</td>
                <td>@{{reg.student}}</td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
