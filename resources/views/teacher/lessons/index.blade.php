@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('lessons.index.heading')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('teacher.api.lessons')}}"
          @if($teachers)
          :teachers='@json($teachers)'
          @endif
          default-start-date='{{$defaultStartDate}}'
          default-end-date='{{$defaultEndDate}}'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          error-text="@lang('lessons.index.error')">
        <div slot="empty" class="alert alert-warning">@lang('lessons.index.none')</div>
        <template scope="props">
          <div class="table-responsive">
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
                <th>@lang('messages.participants')</th>
                <th></th>
              </tr>
              </thead>
              <tr v-for="lesson in props.data" :class="{'text-muted': lesson.cancelled}">
                <td>@{{$d(moment(lesson.date), 'short')}}</td>
                <td>@{{$t('messages.range', lesson.time)}}</td>
                @if($teachers)
                  <td>@{{lesson.teacher}}</td>
                @endif
                <td>
                  <a v-if="lesson.course" :href="'{{route('teacher.courses.show', '')}}/' + lesson.course.id">@{{lesson.course.name}}</a>
                </td>
                <td>@{{lesson.course ? lesson.course.room : lesson.room}}</td>
                <td>@{{lesson.students}}<span v-if="lesson.maxstudents">/@{{lesson.maxstudents}}</span></td>
                <td>
                  <a :href="'{{route('teacher.lessons.show', '')}}/' + lesson.id">@lang('lessons.index.details')</a>
                </td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
