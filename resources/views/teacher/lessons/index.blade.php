@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('lessons.lessons')</h2>
          <div class="panel-body">
            <div class="clearfix">
              @if($isAdmin)
                <div class="form-group col-sm-3">
                  <label for="teacher">@lang('messages.teacher')</label>
                  <select class="form-control" v-model="teacher">
                    @foreach($teachers as $teacher)
                      <option value="{{$teacher->id}}">{{$teacher->name()}}</option>
                    @endforeach
                  </select>
                </div>
              @endif


              <daterange min-date="{{$minDate->toDateString()}}"
                         max-date="{{$maxDate->toDateString()}}"
                         {{--:disabled-days-of-week="{{json_encode($disabledDaysOfWeek)}}"--}}
                         {{--:disabled-dates="{{json_encode($offdays)}}"--}}
                         :old-first-date="{{old('firstDate') ?: 'null'}}"
                         :old-last-date="{{old('lastDate') ?: 'null'}}"
                         :type="1"
                         v-on:first="setFirstDate"
                         v-on:last="setLastDate">
                <template slot="label-first">@lang('courses.firstDate')</template>
                <template slot="label-last">@lang('courses.lastDate')</template>
              </daterange>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
