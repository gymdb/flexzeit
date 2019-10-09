@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('registrations.missing.headingSport')</h2>
    <div class="panel-body">
      <teacher-absent inline-template>
        <filtered-list ref="filter"
            url="{{route('teacher.api.registrations.missingSportsRegistration')}}"
            :groups='@json($groups)'
            default-start-date='{{$defaultStartDate->toDateString()}}'
            default-end-date='{{$defaultEndDate->toDateString()}}'
            min-date='{{$minDate->toDateString()}}'
            max-date='{{$maxDate->toDateString()}}'
            :disabled-days-of-week='@json($disabledDaysOfWeek)'
            :disabled-dates='@json($offdays)'
            :require-group='false'
            :require-student="false"
            error-text="@lang('registrations.missing.error')">
          <div slot="empty" class="alert alert-warning">@lang('registrations.missing.none')</div>
          <template scope="props">
               <div class="table-responsive">
              <table class="table table-squeezed">
                <thead>
                <tr>
                  <th>@lang('messages.student')</th>
                  @if($isAdmin)
                    <th class="hidden-print"></th>
                  @endif
                </tr>
                </thead>
                <tr v-for="item in props.data">
                  <td>@{{item.name}}</td>
                </tr>
              </table>
            </div>
          </template>
        </filtered-list>
      </teacher-absent>
    </div>
  </section>
@endsection
