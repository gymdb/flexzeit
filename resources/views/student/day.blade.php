@extends('layouts.app')

@section('content')
  <student-register inline-template>
    <main class="container">
      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          @component('errors', ['errorMin' => 20, 'errorMax' => 25])
            @slot('successMsg')
              <p><strong>@lang('registrations.success')</strong></p>
            @endslot
            @slot('partialMsg')
              <p><strong>@lang('registrations.partial')</strong></p>
            @endslot
            @slot('failureMsg')
              <p><strong>@lang('registrations.failure')</strong></p>
            @endslot
          @endcomponent

          <section class="panel panel-default">
            <h2 class="panel-heading">@lang('registrations.on', ['date' => $date])</h2>
            <div class="panel-body">
              @if(empty($registrations))
                @lang('registrations.today.none')
              @else
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                    <tr>
                      <th>@lang('messages.time')</th>
                      <th>@lang('messages.teacher')</th>
                      <th>@lang('messages.course')</th>
                      <th>@lang('messages.room')</th>
                    </tr>
                    </thead>
                    @foreach($registrations as $slot)
                      <tr>
                        <td>{{$slot['start']}} &ndash; {{$slot['end']}}</td>
                        @if(!$slot['lesson'])
                          <td colspan="3" class="text-danger">@lang('registrations.missing')</td>
                        @elseif($slot['lesson']->course)
                          <td>{{$slot['lesson']->teacher->name()}}</td>
                          <td>{{$slot['lesson']->course->name}}</td>
                          <td>{{$slot['lesson']->course->room}}</td>
                        @else
                          <td>{{$slot['lesson']->teacher->name()}}</td>
                          <td></td>
                          <td>{{$slot['lesson']->room}}</td>
                        @endif
                      </tr>
                    @endforeach
                  </table>
                </div>
              @endif
            </div>
          </section>

          @if(!empty($lessons))
            <section class="panel panel-default">
              <h2 class="panel-heading">@lang('registrations.available')</h2>
              <div class="panel-body">
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                    <tr>
                      <th>@lang('messages.time')</th>
                      <th>@lang('messages.teacher')</th>
                      <th>@lang('messages.course')</th>
                      <th>@lang('messages.room')</th>
                      @if($allowRegistration)
                        <th></th>
                      @endif
                    </tr>
                    </thead>
                    @foreach($lessons as $slot)
                      <tr>
                        <td>{{$slot['start']}} &ndash; {{$slot['end']}}</td>
                        <td>{{$slot['lesson']->teacher->name()}}</td>
                        @if($slot['lesson']->course)
                          <td>{{$slot['lesson']->course->name}}</td>
                          <td>
                            @if(empty($slot['lesson']->course->room))
                              {{$slot['lesson']->room}}
                            @else
                              {{$slot['lesson']->course->room}}
                            @endif
                          </td>
                          @if($allowRegistration)
                            <td>
                              <a href="#" title="@lang('registrations.register')"
                                 @click.prevent="registerCourse({{$slot['lesson']->course->id}}, '{{$slot['lesson']->course->name}}'
                              , '{{$slot['lesson']->teacher->name()}}', {{json_encode($slot['lessons'])}})">
                                <span class="sr-only">@lang('registrations.register')</span>
                                <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                              </a>
                            </td>
                          @endif
                        @else
                          <td></td>
                          <td>{{$slot['lesson']->room}}</td>
                          @if($allowRegistration)
                            <td>
                              <a href="#" title="@lang('registrations.register')"
                                 @click.prevent="registerLesson('{{$slot['lesson']->teacher->name()}}', {{json_encode($slot['lessons'])}})">
                                <span class="sr-only">@lang('registrations.register')</span>
                                <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                              </a>
                            </td>
                          @endif
                        @endif
                      </tr>
                    @endforeach
                  </table>
                </div>
              </div>
            </section>
          @endif
        </div>
      </div>

      <modal :value="modal" effect="fade" ok-text="@lang('registrations.register')" cancel-text="@lang('messages.cancel')" @cancel="cancel" :callback=
      "save">
      <template v-if="isCourse">
        <template slot="title">@lang('registrations.modal.title')</template>
        <p>@lang('registrations.course.info')</p>
        <ul>
          <li v-for="date in dates">@{{date}}</li>
        </ul>
      </template>
      <template v-else>
        <template slot="title">@lang('registrations.lesson.title')</template>
        <p>@lang('registrations.lesson.info', ['date' => $date])</p>
        <ul>
          <li v-for="lesson in lessons" class="list-unstyled">
            <label>
              <input type="checkbox" :value="lesson.id" v-model="chosen" ref="checked"/>
              @{{lesson.start}} &ndash; @{{lesson.end}}
            </label>
          </li>
        </ul>
      </template>
      </modal>
    </main>
  </student-register>
@endsection
