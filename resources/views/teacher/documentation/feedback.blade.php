@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('registrations.feedback.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('teacher.api.feedback')}}"
          :groups='@json($groups)'
          :teachers='@json($teachers)'
          :subjects='@json($subjects)'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          error-text="@lang('registrations.feedback.error')">
        <div slot="empty" class="alert alert-warning">@lang('registrations.feedback.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.feedback')</th>
              </tr>
              </thead>
              <tr v-for="reg in props.data">
                <td>@{{$d(moment(reg.date), 'short')}}</td>
                <td>@{{reg.teacher}}</td>
                <td class="wrap">@{{reg.feedback}}</td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
