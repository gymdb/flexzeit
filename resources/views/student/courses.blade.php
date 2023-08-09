@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('nav.courses.next')</h2>
    <div class="panel-body">
      <filtered-list
          url="{{route('student.api.courses')}}"
          :teachers='@json($teachers)'
          default-start-date='{{$defaultStartDate->toDateString()}}'
          default-end-date='{{$defaultEndDate->toDateString()}}'
          min-date='{{$minDate->toDateString()}}'
          max-date='{{$maxDate->toDateString()}}'
          :disabled-days-of-week='@json($disabledDaysOfWeek)'
          :disabled-dates='@json($offdays)'
          error-text="@lang('courses.index.error')">
        <div slot="empty" class="alert alert-warning">@lang('courses.index.none')</div>
        <template scope="props">
          <div class="table-responsive">
            <table class="table">
              <thead>
              <tr>
                <th>@lang('messages.date')</th>
                <th>@lang('messages.time')</th>
                <th>@lang('messages.teacher')</th>
                <th>@lang('messages.course')</th>
                <th>@lang('messages.participants')</th>
              </tr>
              </thead>
              <tr v-for="(course, key) in props.data" :class="'category'+{{'course.category'}} ">
                <td>
                  <a :href="'{{route('student.day', '')}}/' + course.first">
                    @{{$d(moment(course.first), 'short')}}
                  </a>
                  <span v-if="course.last">&ndash; @{{$d(moment(course.last), 'short')}}</span>
                </td>
                <td>@{{$t('messages.range', course.time)}}</td>
                <td class="course">

                  <div :id="'popover-'+key" ref="'popover-'+key" >@{{course.teacher.name}}</div>
                  <b-popover :target="'popover-'+key" triggers="hover focus" placement="right" >
                  <!--<div slot="content"> -->
                    <p v-if="course.teacher.subjects">@{{course.teacher.subjects}}</p>
                    <p v-if="course.teacher.info">@{{course.teacher.info}}</p>
                    <p>
                      <img class="popover-image" src="{{url('/images/avatar.png')}}"
                           :src="course.teacher.image || '{{url('/images/avatar.png')}}'"
                           @load="$refs['popover-' + key][0].position()"/>
                    </p>
                  <!--</div> -->
                  </b-popover>

                </td>
                <td class="course">
                  <span :id="'desc-'+key" ref="'popover-'+key">@{{course.name}}</span>
                  <b-popover v-if="course.description" triggers="hover" :target="'desc-'+key" placement="right">
                    <div slot="content"> @{{course.description}}</div>
                    <span>@{{course.description}}</span>
                  </b-popover>
                  <b-popover v-else triggers="hover" :target="'desc-'+key" placement="right">@{{course.name}}</b-popover>
                </td>
                <td>@{{course.students}}<span
                      v-if="course.maxstudents">/@{{course.maxstudents}}</span></td>
              </tr>
            </table>
          </div>
        </template>
      </filtered-list>
    </div>
  </section>
@endsection
