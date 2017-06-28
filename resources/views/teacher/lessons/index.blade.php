@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('lessons.list.header')</h2>
          <teacher-lessons inline-template>
            <div class="panel-body">
              <filter-options
                  @if($teachers)
                  :teachers='@json($teachers)'
                  teacher-label="@lang('messages.teacher')"
                  @endif
                  default-start-date='{{$defaultStartDate}}'
                  default-end-date='{{$defaultEndDate}}'
                  min-date='{{$minDate->toDateString()}}'
                  max-date='{{$maxDate->toDateString()}}'
                  :disabled-days-of-week='@json($disabledDaysOfWeek)'
                  :disabled-dates='@json($offdays)'
                  error-text="@lang('messages.error')"
                  start-label="@lang('messages.from')"
                  end-label="@lang('messages.to')"
                  v-on:filter="loadData"></filter-options>

              <error :error="error"></error>

              <div v-if="!lessons || !lessons.length" class="alert alert-warning">@lang('lessons.list.noMatching')</div>
              <div v-else class="table-responsive">
                <table class="table table-squeezed">
                  <thead>
                  <tr>
                    <th>@lang('messages.date')</th>
                    <th>@lang('messages.time')</th>
                    @if($teachers)
                      <th>@lang('messages.teacher')</th>
                    @endif
                    <th>@lang('messages.course')</th>
                    <th>@lang('messages.room')</th>
                    <th></th>
                  </tr>
                  </thead>
                  <tr v-for="lesson in lessons">
                    <td>@{{lesson.date}}</td>
                    <td>@{{lesson.start}} &ndash; @{{lesson.end}}</td>
                    @if($teachers)
                      <td>@{{lesson.teacher}}</td>
                    @endif
                    <td>
                      <a v-if="lesson.course" :href="'{{route('teacher.courses.show', '')}}/' + lesson.course.id">@{{lesson.course.name}}</a>
                    </td>
                    <td>@{{lesson.course ? lesson.course.room : lesson.room}}</td>
                    <td>
                      <a :href="'{{route('teacher.lessons.show', '')}}/' + lesson.id">@lang('lessons.details')</a>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </teacher-lessons>
        </section>
      </div>
    </div>
  </main>
@endsection
