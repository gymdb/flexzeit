@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('courses.create.' . $type . 'heading')</h2>
    <div class="panel-body">
      <course-create inline-template
                     :lessons='@json($lessons)'
                     :rooms='@json($rooms)'
                     :old-number='@json($oldNumber)'
                     old-name="{{old('name')}}"
                     :old-room='@json($oldRoom)'
                     :old-teacher='@json($oldTeacher)'
                     @if($obligatory)
                     obligatory
                     :old-subject='@json($oldSubject)'
                     :old-groups='@json($oldGroups)'
                     :groups='@json($groups)'
                     @else
                     :old-max-students='@json($oldMaxStudents)'
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

            @if($teachers)
              <div class="form-group col-sm-3 col-xs-12">
                <label for="teacher">@lang('messages.teacher')</label>
                <select id="teacher" name="teacher" v-model="teacher" class="form-control">
                  <option :value="null">@lang('messages.teacher')</option>
                  @foreach($teachers as $teacher)
                    <option :value="{{$teacher['id']}}">{{$teacher['name']}}</option>
                  @endforeach
                </select>
              </div>
              <div class="clearfix"></div>
            @endif

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

            <div class="form-group col-sm-3 col-xs-12">
              <label for="frequency">@lang('courses.data.frequency')</label>
              <b-form-select v-model="frequency" name="frequency" class="form-control" :placeholder="$tc('courses.frequency', 0)" :disabled="frequencyDisabled"
                        :options="frequencyOptions">
              </b-form-select>
            </div>

            <div class="form-group col-sm-3 col-xs-12 required">
              <label>@lang('courses.data.lesson')</label>
              <div v-if="lessonsOnDay">
                <label v-for="(time, n) in lessonsOnDay" class="radio-inline">
                  <input type="radio" name="lessonNumber" :value="n" v-model="number" required/> @{{$t('messages.range', {number: n})}}
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
                    <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}: @{{lesson.course}}
                  </li>
                </ul>
              </div>
              @if($obligatory)
                <div v-else-if="withObligatory.length" class="alert alert-danger">
                  <strong>@lang('courses.create.obligatory.withObligatory')</strong>
                  <ul>
                    <li v-for="lesson in withObligatory">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}:
                      @{{lesson.groups.join(', ')}} (@{{lesson.course}})
                    </li>
                  </ul>
                </div>
                <div v-else-if="timetable.length" class="alert alert-danger">
                  <strong>@lang('courses.create.obligatory.timetable')</strong>
                  <ul>
                    <li v-for="group in timetable">
                      @{{group}}
                    </li>
                  </ul>
                </div>
                <div v-else-if="offdays.length" class="alert alert-danger">
                  <strong>@lang('courses.create.obligatory.offdays')</strong>
                  <ul>
                    <li v-for="offday in offdays">
                      <strong>@{{$d(moment(offday.date), 'short')}}</strong>: @{{offday.group}}
                    </li>
                  </ul>
                </div>
              @endif
              <div v-else-if="forNewCourse.length">
                <div class="alert alert-info">
                  <strong>@lang('courses.create.forNewCourse')</strong>
                  <ul>
                    <li v-for="lesson in forNewCourse">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                      <strong v-if="!lesson.exists">(@lang('courses.create.additional'))</strong>
                    </li>
                  </ul>
                </div>

                <div v-if="cancelled.length" class="alert alert-warning">
                  <strong>@lang('courses.create.cancelled.warning')</strong>
                  <ul>
                    <li v-for="lesson in cancelled">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                    </li>
                  </ul>
                </div>

                <div v-if="occupied.length" class="alert alert-warning">
                  <strong>@lang('courses.create.occupied')</strong>
                  <ul>
                    <li v-for="lesson in occupied">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}: @{{lesson.teacher}}
                    </li>
                  </ul>
                </div>
              </div>
              <div v-else-if="cancelled.length" class="alert alert-danger">
                <strong>@lang('courses.create.cancelled.error')</strong>
                <ul>
                  <li v-for="lesson in cancelled">
                    <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                  </li>
                </ul>
              </div>
            </div>

            <div class="form-group col-sm-6 col-xs-12 required">
              <label for="name">@lang('courses.data.name')</label>
              <input id="name" name="name" class="form-control" v-model.trim="name" maxlength="50" required
                     placeholder="@lang('courses.data.name')"/>
            </div>

            <div class="form-group col-sm-6 col-xs-12 required">
              <label for="room">@lang('courses.data.room')</label>
              <b-form-select v-model="room" name="room" class="form-control" placeholder="@lang('courses.data.selectRoom')" search
                        :options="parsedRooms" options-value="id"></b-form-select>
            </div>

            <div class="form-group col-xs-12">
              <label for="description">@lang('courses.data.description')</label>
              <textarea id="description" name="description" class="form-control"
                        placeholder="@lang('courses.data.description')">{{old('description')}}</textarea>
            </div>

            @if($obligatory)
              <div class="form-group col-sm-3 col-xs-12 required">
                <label for="subject">@lang('courses.data.subject')</label>
                <select id="subject" name="subject" v-model="subject" class="form-control">
                  <option :value="null">@lang('courses.data.selectSubject')</option>
                  @foreach($subjects as $subject)
                    <option :value="{{$subject['id']}}">{{$subject['name']}}</option>
                  @endforeach
                </select>

                <!--<b-form-select name="subject" class="select-container" placeholder="@lang('courses.data.selectSubject')" search
                          v-model="subject" :options='@json($subjects)' options-value="id" options-label="name"></b-form-select> -->
              </div>

              <div class="form-group col-sm-3 col-xs-12 required">
                <label>@lang('courses.data.groups')</label>
                <b-form-select name="groups[]"  class="multiselect" multiple :select-size="4" v-model="groups">
                  <option :value=null>@lang('courses.data.selectGroups')</option>
                  <option v-for="group in {{$groups}}" :value="group.id">
                    @{{group.name}}
                  </option>
                </b-form-select>
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
                <input type="number" v-model.number="maxStudents" min="1" id="maxStudents" name="maxStudents" class="form-control"
                       placeholder="@lang('courses.data.maxStudents')"/>
              </div>
            @endif

            <div class="form-group col-sm-3 col-xs-12 required">
              <label for="category">@lang('courses.data.category')</label>
              <b-form-select v-model="category" name="category" class="form-control" placeholder="@lang('courses.data.selectCategory')"
                        :options="categoryOptionsList"></b-form-select>
            </div>

            <div class="col-xs-12">
              <button class="btn btn-success" :disabled="buttonDisabled">@lang('courses.create.submit')</button>
            </div>
          </div>
        </form>
      </course-create>
    </div>
  </section>
@endsection
