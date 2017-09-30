@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('bugreports.index.heading')</h2>
    <div class="panel-body">
      <teacher-bugreports inline-template>
        <filtered-list ref="filter" has-trashed
                       url="{{route('teacher.api.bugreports')}}"
                       min-date='{{$minDate->toDateString()}}'
                       max-date='{{$maxDate->toDateString()}}'
                       error-text="@lang('bugreports.index.error')">
          <div slot="empty" class="alert alert-warning">@lang('bugreports.index.none')</div>
          <template scope="props">
            <error :error="error">@lang('bugreports.index.trashError')</error>

            <div class="table-responsive">
              <table class="table">
                <thead>
                <tr>
                  <th>@lang('bugreports.data.created')</th>
                  <th>@lang('bugreports.data.author')</th>
                  <th>@lang('bugreports.data.text')</th>
                  <th></th>
                </tr>
                </thead>
                <tr v-for="report in props.data" :class="{'text-muted': report.trashed}">
                  <td>@{{$d(moment(report.date), 'datetime')}}</td>
                  <td>@{{report.author}}</td>
                  <td class="wrap">@{{report.text}}</td>
                  <td>
                    <teacher-trash-report :id="report.id" :trashed="report.trashed" @success="refresh" @error="setError"></teacher-trash-report>
                  </td>
                </tr>
              </table>
            </div>
          </template>
        </filtered-list>
      </teacher-bugreports>
    </div>
  </section>
@endsection
