@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('messages.today', ['date' => \App\Helpers\Date::today()])</h2>
          <documentation inline-template>
            <div class="panel-body">
              <filter-options
                  :teachers="{{json_encode($teachers)}}"
                  :groups="{{json_encode($groups)}}"
                  :subjects="{{json_encode($subjects)}}"
                  min-date="{{$minDate->toDateString()}}"
                  max-date="{{$maxDate->toDateString()}}"
                  :disabled-days-of-week="{{json_encode($disabledDaysOfWeek)}}"
                  :disabled-dates-of-week="{{json_encode($offdays)}}"
                  error-text="@lang('messages.error')"
                  teacher-label="@lang('messages.teacher')"
                  group-label="@lang('messages.group')"
                  student-label="@lang('messages.student')"
                  subject-label="@lang('messages.subject')"
                  date-from-label="@lang('courses.firstDate')"
                  date-to-label="@lang('courses.lastDate')"
                  v-on:filter="loadData"></filter-options>

              <error :error="error"></error>

              <p v-if="!registrations">Choose student</p>
              <p v-else-if="!registrations.length">No lessons</p>
              <div v-else class="table-responsive">
                <table class="table table-squeezed">
                  <thead>
                  <tr>
                    <th>@lang('messages.date')</th>
                    <th>@lang('messages.teacher')</th>
                    <th>@lang('messages.documentation')</th>
                  </tr>
                  </thead>
                  <tr v-for="reg in registrations">
                    <td>@{{reg.lesson.date}}</td>
                    <td>@{{reg.teacher}}</td>
                    <td>@{{reg.documentation || 'No documentation'}}</td>
                  </tr>
                </table>
              </div>

            </div>
          </documentation>
        </section>
      </div>
    </div>
  </main>
@endsection
