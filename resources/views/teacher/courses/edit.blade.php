@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('courses.edit.' . $type . 'heading')</h2>
    <div class="panel-body">
      <course-edit inline-template
                   :lessons='@json($lessons)'
                   :rooms='@json($rooms)'
                   :data='@json($courseData)'
                   :old='@json($old)'
                   @if($obligatory)
                   obligatory
                   :groups='@json($groups)'
                   @else
                   :min-year="{{$minYear}}"
                   :max-year="{{$maxYear}}"
          @endif>
        <form class="clearfix" action="{{route('teacher.courses.' . $type . 'update', [$courseData['id']])}}" method="post">
          {{method_field('put')}}
          {{csrf_field()}}

          <div class="row clearfix">
            <div class="col-xs-12">
              @if(count($errors) > 0)
                <div class="alert alert-danger">
                  <strong>@lang('courses.edit.saveError')</strong>
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
                       old-first-date="{{$courseData['firstDate']}}"
                       old-last-date="{{$old['lastDate']}}"
                       label-first="@lang('courses.data.firstDate')"
                       label-last="@lang('courses.data.lastDate')"
                       :type="{{$allowDateChange ? 2 : 3}}"
                       v-on:last="setLastDate">
            </daterange>

            <div class="form-group col-sm-3 col-xs-12">
              <label for="frequency">@lang('courses.data.frequency')</label>
              <input type="text" id="frequency" name="frequency" class="form-control" disabled :value="frequencyLabel">
            </div>

            <div class="form-group col-sm-3 col-xs-12">
              <label>@lang('courses.data.lesson')</label>
              <div>
                @foreach($lessons as $n=>$time)
                  <label class="radio-inline">
                    <input type="radio" name="lessonNumber" value="{{$n}}" disabled @if($n === $courseData['number']) checked @endif/>
                    @lang('messages.format.range', ['number' => $n])
                  </label>
                @endforeach
              </div>
            </div>

            <div class="col-xs-12">
              <error :error="error">@lang('courses.edit.loadError')</error>

              <div v-if="withCourse.length" class="alert alert-danger">
                <strong>@lang('courses.edit.withCourse')</strong>
                <ul>
                  <li v-for="lesson in withCourse">
                    <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}: @{{lesson.course}}
                  </li>
                </ul>
              </div>
              @if($obligatory)
                <div v-else-if="withObligatory.length" class="alert alert-danger">
                  <strong>@lang('courses.edit.obligatory.withObligatory')</strong>
                  <ul>
                    <li v-for="lesson in withObligatory">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}:
                      @{{lesson.groups.join(', ')}} (@{{lesson.course}})
                    </li>
                  </ul>
                </div>
                <div v-else-if="timetable.length" class="alert alert-danger">
                  <strong>@lang('courses.edit.obligatory.timetable')</strong>
                  <ul>
                    <li v-for="group in timetable">
                      @{{group}}
                    </li>
                  </ul>
                </div>
                <div v-else-if="offdays.length" class="alert alert-danger">
                  <strong>@lang('courses.edit.obligatory.offdays')</strong>
                  <ul>
                    <li v-for="offday in offdays">
                      <strong>@{{$d(moment(offday.date), 'short')}}</strong>: @{{offday.group}}
                    </li>
                  </ul>
                </div>
              @endif
              <div v-else>
                <div v-if="added.length" class="alert alert-info">
                  <strong>@lang('courses.edit.addedLessons')</strong>
                  <ul>
                    <li v-for="lesson in added">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                      <strong v-if="!lesson.exists">(@lang('courses.edit.additional'))</strong>
                    </li>
                  </ul>
                </div>
                <div v-else-if="removed.length" class="alert alert-info">
                  <strong>@lang('courses.edit.removedLessons')</strong>
                  <ul>
                    <li v-for="lesson in removed">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                    </li>
                  </ul>
                </div>

                <div v-if="cancelled.length" class="alert alert-warning">
                  <strong>@lang('courses.edit.cancelled')</strong>
                  <ul>
                    <li v-for="lesson in cancelled">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}
                    </li>
                  </ul>
                </div>

                <div v-if="occupied.length" class="alert alert-warning">
                  <strong>@lang('courses.edit.occupied')</strong>
                  <ul>
                    <li v-for="lesson in occupied">
                      <strong>@{{$d(moment(lesson.date), 'short')}}</strong>, @{{$t('messages.range', lesson.time)}}: @{{lesson.teacher}}
                    </li>
                  </ul>
                </div>
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
              <textarea id="description" name="description" class="form-control" v-model.trim="description"
                        placeholder="@lang('courses.data.description')"></textarea>
            </div>

            @if($obligatory)
              <div class="form-group col-sm-3 col-xs-12 required">
                <label for="subject">@lang('courses.data.subject')</label>
                <select id="room" name="subject" v-model="subject" class="form-control">
                  <option :value="null">@lang('courses.data.selectSubject')</option>
                  @foreach($subjects as $subject)
                    <option :value="{{$subject['id']}}">{{$subject['name']}}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group col-sm-3 col-xs-12 required">
                <label for="groups">@lang('courses.data.groups')</label>
                @if($allowGroupsChange)
                  <!--<b-form-select name="groups[]" class="form-control" placeholder="@lang('courses.data.selectGroups')" multiple search
                            v-model="groups" :options='@json($groups)' options-value="id" options-label="name"
                            @if(!$allowGroupsChange) readonly @endif></b-form-select> -->
                  <b-form-select name="groups[]"  class="multiselect" multiple :select-size="4" v-model="groups">
                    <option :value=null>@lang('courses.data.selectGroups')</option>
                    <option v-for="group in {{$groups}}" :value="group.id">
                      @{{group.name}}
                    </option>
                  </b-form-select>
                @else
                  <input id="groups" class="form-control" value="{{$groupNames}}" readonly/>
                  @foreach($courseData['groups'] as $group)
                    <input name="groups[]" type="hidden" value="{{$group}}"/>
                  @endforeach
                @endif
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
                <input type="number" v-model.number="maxStudents" id="maxStudents" name="maxStudents" class="form-control" min="1"
                       placeholder="@lang('courses.data.maxStudents')"/>
              </div>
            @endif
            <div class="form-group col-sm-3 col-xs-12 required">
              <label for="category">@lang('courses.data.category')</label>
              <b-form-select v-model="category" name="category" class="form-control" placeholder="@lang('courses.data.selectCategory')"
                        :options="categoryOptionsList" v-model.number="category" id="category"></b-form-select>
            </div>

            <div class="col-xs-12">
              <button class="btn btn-success" :disabled="buttonDisabled">@lang('courses.edit.submit')</button>
            </div>
          </div>
        </form>
      </course-edit>
    </div>
  </section>
@endsection
