@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('courses.create')</h2>
          <div class="panel-body">
            <course-create inline-template
                           :lessons="{{json_encode($lessons)}}"
                           :min-year="{{$minYear}}"
                           :max-year="{{$maxYear}}"
                           :old-name="{{json_encode(old('name'))}}"
                           :old-room="{{json_encode(old('room'))}}"
                           :old-first-date="{{json_encode(old('firstDate'))}}"
                           :old-last-date="{{json_encode(old('lastDate'))}}"
                           :old-year-from="{{json_encode(old('yearFrom'))}}"
                           :old-year-to="{{json_encode(old('yearTo'))}}">
              <form class="clearfix" action="{{route('teacher.courses.store')}}" method="post">
                {{csrf_field()}}

                <div class="row clearfix">
                  <div class="col-xs-12">
                    @if (count($errors) > 0)
                      <div class="alert alert-danger">
                        @lang('course.error.create')
                        <ul>
                          @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                          @endforeach
                        </ul>
                      </div>
                    @endif
                  </div>

                  <daterange min-date="{{$minDate->toDateString()}}"
                             max-date="{{$maxDate->toDateString()}}"
                             :disabled-days-of-week="{{json_encode($disabledDaysOfWeek)}}"
                             :disabled-dates="{{json_encode($offdays)}}"
                             label-first="@lang('courses.firstDate')"
                             label-last="@lang('courses.lastDate')"
                             v-on:first="setFirstDate"
                             v-on:last="setLastDate">
                  </daterange>

                  <div class="form-group col-sm-6 col-xs-12 required">
                    <label>@lang('courses.lesson')</label>
                    <div v-if="lessonsOnDay">
                      <label v-for="(lesson, n) in lessonsOnDay" class="checkbox-inline">
                        <input type="radio" name="lessonNumber" :value="n" v-model="number" required/>
                        @{{lesson.start}} &ndash; @{{lesson.end}}
                      </label>
                    </div>
                    <p v-else class="form-control-static">@lang('courses.chooseDay')</p>
                  </div>

                  <div class="col-xs-12">
                    <error :error="error">@lang('course.error.loading')</error>
                    <div v-if="lessonsWithCourse.length" class="alert alert-danger">
                      <strong>@lang('courses.lessonsWithCourse')</strong>
                      <ul>
                        <li v-for="lesson in lessonsWithCourse">
                          <strong>@{{lesson.date}}</strong>, @{{lessonsOnDay[lesson.number].start}} &ndash; @{{lessonsOnDay[lesson.number].end}}:
                          @{{lesson.course}}
                        </li>
                      </ul>
                    </div>
                    <div v-else-if="lessonsForNewCourse.length" class="alert alert-info">
                      <strong>@lang('courses.lessonsForNewCourse')</strong>
                      <ul>
                        <li v-for="lesson in lessonsForNewCourse">
                          <strong>@{{lesson.date}}</strong>, @{{lessonsOnDay[lesson.number].start}} &ndash; @{{lessonsOnDay[lesson.number].end}}
                        </li>
                      </ul>
                    </div>
                  </div>

                  <div class="form-group col-sm-6 col-xs-12 required">
                    <label for="name">@lang('courses.name')</label>
                    <input type="text" id="name" name="name" class="form-control" v-model.trim="name" maxlength="50" required
                           placeholder="@lang('courses.name')"/>
                  </div>

                  <div class="form-group col-sm-6 col-xs-12 required">
                    <label for="room">@lang('courses.room')</label>
                    <input type="text" id="room" name="room" class="form-control" v-model.trim="room" maxlength="50" required
                           placeholder="@lang('courses.room')"/>
                  </div>

                  <div class="form-group col-xs-12">
                    <label for="description">@lang('courses.description')</label>
                    <textarea id="description" name="description" class="form-control"
                              placeholder="@lang('courses.description')">{{old('description')}}</textarea>
                  </div>

                  <div class="form-group col-sm-3 col-xs-12 ">
                    <label for="yearFrom">@lang('courses.year.from')</label>
                    <input type="number" v-model.number="yearFrom" :min="minYear" :max="maxYearFrom" id="yearFrom" name="yearFrom"
                           class="form-control" placeholder="@lang('courses.year.from')"/>
                  </div>

                  <div class="form-group col-sm-3 col-xs-12 ">
                    <label for="yearTo">@lang('courses.year.to')</label>
                    <input type="number" v-model.number="yearTo" :min="minYearTo" :max="maxYear" id="yearTo" name="yearTo" class="form-control"
                           placeholder="@lang('courses.year.to')"/>
                  </div>

                  <div class="form-group col-sm-3 col-xs-12 ">
                    <label for="maxStudents">@lang('courses.maxStudents')</label>
                    <input type="number" id="maxStudents" name="maxStudents" class="form-control" min="1" value="{{old('maxStudents')}}"
                           placeholder="@lang('courses.maxStudents')"/>
                  </div>

                  <div class="col-xs-12">
                    <button type="submit" class="btn btn-success" :disabled="buttonDisabled">@lang('courses.create')</button>
                  </div>
                </div>
              </form>
            </course-create>
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
