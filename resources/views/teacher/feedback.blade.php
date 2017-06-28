@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('messages.feedback.link')</h2>
          <feedback-show inline-template>
            <div class="panel-body">
              <filter-options
                  :teachers='@json($teachers)'
                  :groups='@json($groups)'
                  :subjects='@json($subjects)'
                  min-date='{{$minDate->toDateString()}}'
                  max-date='{{$maxDate->toDateString()}}'
                  :disabled-days-of-week='@json($disabledDaysOfWeek)'
                  :disabled-dates='@json($offdays)'
                  error-text="@lang('messages.error')"
                  teacher-label="@lang('messages.teacher')"
                  group-label="@lang('messages.group')"
                  student-label="@lang('messages.student')"
                  subject-label="@lang('messages.subject')"
                  start-label="@lang('messages.from')"
                  end-label="@lang('messages.to')"
                  v-on:filter="loadData"></filter-options>

              <error :error="error"></error>

              <div v-if="!registrations" class="alert alert-info">@lang('messages.chooseStudent')</div>
              <div v-else-if="!registrations.length" class="alert alert-warning">@lang('feedback.noMatching')</div>
              <div v-else class="table-responsive">
                <table class="table table-squeezed">
                  <thead>
                  <tr>
                    <th>@lang('messages.date')</th>
                    <th>@lang('messages.teacher')</th>
                    <th>@lang('messages.feedback.header')</th>
                  </tr>
                  </thead>
                  <tr v-for="reg in registrations">
                    <td>@{{reg.lesson.date}}</td>
                    <td>@{{reg.teacher}}</td>
                    <td class="wrap">@{{reg.feedback}}</td>
                  </tr>
                </table>
              </div>

            </div>
          </feedback-show>
        </section>
      </div>
    </div>
  </main>
@endsection
