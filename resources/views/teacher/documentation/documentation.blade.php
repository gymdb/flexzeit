@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('registrations.documentation.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url="/teacher/api/documentation"
          :groups='@json($groups)'
          :teachers='@json($teachers)'
          :subjects='@json($subjects)'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          error-text="@lang('registrations.documentation.error')">
        <div slot="empty" class="alert alert-warning">@lang('registrations.documentation.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th v-if="!props.filter.teacher">@lang('messages.teacher')</th>
                <th>@lang('messages.documentation')</th>
              </tr>
              </thead>
              <tr v-for="reg in props.data">
                <td>@{{$d(moment(reg.date), 'short')}}</td>
                <td v-if="!props.filter.teacher">@{{reg.teacher}}</td>
                <td class="wrap">@{{reg.documentation || '@lang('registrations.documentation.empty')'}}</td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
