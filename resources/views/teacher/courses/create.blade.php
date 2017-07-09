@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('courses.create.' . $type . 'heading')</h2>
    <div class="panel-body">
      <course-create inline-template
                     :lessons='@json($lessons)'
                     :old-number='@json($oldNumber)'
                     old-name="{{old('name')}}"
                     old-room="{{old('room')}}"
                     @if($obligatory)
                     obligatory
                     :old-subject='@json($oldSubject)'
                     :old-groups='@json($oldGroups)'
                     @else
                     :min-year="{{$minYear}}"
                     :max-year="{{$maxYear}}"
                     :old-year-from='@json($oldYearFrom)'
                     :old-year-to='@json($oldYearTo)'
          @endif>
        <form class="clearfix" action="{{route('teacher.courses.' . $type . 'store')}}" method="post">
          {{csrf_field()}}

          <div class="row clearfix">
            <div class="col-xs-12">
              @if(count($errors) > 0)
                <div class="alert alert-danger">
                  <strong>@lang('courses.create.saveError')</strong>
                  <ul>
                    @foreach($errors->all() as $error)
                      <li>{{$error}}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
            </div>

            <daterange min-date="{{$minDate->toDateString()}}"
                       max-date="{{$maxDate->toDateString()}}"
                       :disabled-days-of-week='@json($disabledDaysOfWeek)'
                       :disabled-dates='@json($offdays)'
                       old-first-date="{{$oldFirstDate}}"
                       old-last-date="{{$oldLastDate}}"
                       label-first="@lang('courses.data.firstDate')"
                       label-last="@lang('courses.data.lastDate')"
                       v-on:first="setFirstDate"
                       v-on:last="setLastDate">
            </daterange>

            <div class="form-group col-sm-6 col-xs-12 required">
              <label>@lang('courses.data.lesson')</label>
              <div v-if="lessonsOnDay">
                <label v-for="(time, n) in lessonsOnDay" class="checkbox-inline">
                  <input type="radio" name="lessonNumber" :value="n" v-model="number" required/> @{{$t('messages.range', time)}}
                </label>
              </div>
              <p v-else class="form-control-static">@lang('courses.data.chooseDay')</p>
            </div>

            <div class="col-xs-12">
              <error :error="error">@lang('courses.create.loadError')</error>

              <div v-if="withCourse.length" class="alert alert-danger">
                <strong>@lang('courses.create.withCourse')</strong>
                <ul>
                  <li v-for="lesson in withCourse">
                    <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}: @{{lesson.course.name}}
                  </li>
                </ul>
              </div>
              @if($obligatory)
                <div v-else-if="withObligatory.length" class="alert alert-danger">
                  <strong>@lang('courses.create.obligatory.withObligatory')</strong>
                  <ul>
                    <li v-for="lesson in withObligatory">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}:
                      @{{lesson.course.groups.join(', ')}} (@{{lesson.course.name}})
                    </li>
                  </ul>
                </div>
              @endif
              <div v-else-if="forNewCourse.length" class="alert alert-info">
                <strong>@lang('courses.create.forNewCourse')</strong>
                <ul>
                  <li v-for="lesson in forNewCourse">
                    <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                  </li>
                </ul>
              </div>
            </div>

            <div class="form-group col-sm-6 col-xs-12 required">
              <label for="name">@lang('courses.data.name')</label>
              <input type="text" id="name" name="name" class="form-control" v-model.trim="name" maxlength="50" required
                     placeholder="@lang('courses.data.name')"/>
            </div>

            <div class="form-group col-sm-6 col-xs-12 required">
              <label for="room">@lang('courses.data.room')</label>
              <input type="text" id="room" name="room" class="form-control" v-model.trim="room" maxlength="50" required
                     placeholder="@lang('courses.data.room')"/>
            </div>

            <div class="form-group col-xs-12">
              <label for="description">@lang('courses.data.description')</label>
              <textarea id="description" name="description" class="form-control"
                        placeholder="@lang('courses.data.description')">{{old('description')}}</textarea>
            </div>

            @if($obligatory)
              <div class="form-group col-sm-3 col-xs-12 required">
                <label for="subject">@lang('courses.data.subject')</label>
                <v-select name="subject" class="select-container" placeholder="@lang('courses.data.selectSubject')" search
                          v-model="subject" :options='@json($subjects)' options-value="id" options-label="name"></v-select>
              </div>

              <div class="form-group col-sm-3 col-xs-12 required">
                <label>@lang('courses.data.groups')</label>
                <v-select name="groups[]" class="select-container" placeholder="@lang('courses.data.selectGroups')" multiple search
                          v-model="groups" :options='@json($groups)' options-value="id" options-label="name"></v-select>
              </div>
            @else
              <div class="form-group col-sm-3 col-xs-12 ">
                <label for="yearFrom">@lang('courses.data.year.from')</label>
                <input type="number" v-model.number="yearFrom" :min="minYear" :max="maxYearFrom" id="yearFrom" name="yearFrom"
                       class="form-control" placeholder="@lang('courses.data.year.from')"/>
              </div>

              <div class="form-group col-sm-3 col-xs-12 ">
                <label for="yearTo">@lang('courses.data.year.to')</label>
                <input type="number" v-model.number="yearTo" :min="minYearTo" :max="maxYear" id="yearTo" name="yearTo" class="form-control"
                       placeholder="@lang('courses.data.year.to')"/>
              </div>

              <div class="form-group col-sm-3 col-xs-12 ">
                <label for="maxStudents">@lang('courses.data.maxStudents')</label>
                <input type="number" id="maxStudents" name="maxStudents" class="form-control" min="1" value="{{old('maxStudents')}}"
                       placeholder="@lang('courses.data.maxStudents')"/>
              </div>
            @endif

            <div class="col-xs-12">
              <button type="submit" class="btn btn-success" :disabled="buttonDisabled">@lang('courses.create.submit')</button>
            </div>
          </div>
        </form>
      </course-create>
    </div>
  </section>
@endsection
