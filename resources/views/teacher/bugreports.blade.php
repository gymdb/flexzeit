@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('bugreports.index.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('teacher.api.bugreports')}}"
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          error-text="@lang('bugreports.index.error')">
        <div slot="empty" class="alert alert-warning">@lang('bugreports.index.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table table-squeezed">
              <thead>
              <tr>
                <th>@lang('bugreports.data.created')</th>
                <th>@lang('bugreports.data.author')</th>
                <th>@lang('bugreports.data.text')</th>
              </tr>
              </thead>
              <tr v-for="report in props.data">
                <td>@{{$d(moment(report.date), 'datetime')}}</td>
                <td>@{{report.author}}</td>
                <td>@{{report.text}}</td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
