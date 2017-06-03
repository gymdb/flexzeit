@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('messages.today', ['date' => \App\Helpers\Date::today()])</h2>
          <div class="panel-body">
            @if(empty($today))
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
                  @foreach($today as $slot)
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

        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('registrations.upcoming.title')</h2>
          <div class="panel-body">
            @if(empty($upcoming))
              @lang('registrations.upcoming.none')
            @else
              <div class="table-responsive">
                <table class="table">
                  <thead>
                  <tr>
                    <th>@lang('messages.date')</th>
                    <th>@lang('messages.time')</th>
                    <th>@lang('messages.teacher')</th>
                    <th>@lang('messages.course')</th>
                    <th>@lang('messages.room')</th>
                    <th></th>
                  </tr>
                  </thead>
                  @php $prevDate = null @endphp
                  @foreach($upcoming as $slot)
                    <tr>
                      <td @if($prevDate == $slot['date']) class="invisible" @endif>{{$slot['date']}}</td>
                      <td>{{$slot['start']}} &ndash; {{$slot['end']}}</td>
                      @if(!$slot['lesson'])
                        <td colspan="3" class="text-danger">@lang('registrations.missing')</td>
                        <td>
                          @if($slot['date'] >= $firstRegisterDate)
                            <a href="{{route('student.day', $slot['date']->toDateString())}}" title="@lang('registrations.register')">
                              <span class="sr-only">@lang('registrations.register')</span>
                              <span class="glyphicon glyphicon-circle-arrow-right register-link"></span>
                            </a>
                          @endif
                        </td>
                      @elseif($slot['lesson']->course)
                        <td>{{$slot['lesson']->teacher->name()}}</td>
                        <td>{{$slot['lesson']->course->name}}</td>
                        <td>
                          @if(empty($slot['lesson']->course->room))
                            {{$slot['lesson']->room}}
                          @else
                            {{$slot['lesson']->course->room}}
                          @endif
                        </td>
                        <td>
                          @if($slot['lesson']->course->firstLesson()->date >= $firstRegisterDate && !$slot['registration']->obligatory)
                            <a href="#" title="@lang('registrations.unregister')">
                              <span class="sr-only">@lang('registrations.unregister')</span>
                              <span class="glyphicon glyphicon-remove-sign register-link"></span>
                            </a>
                          @endif
                        </td>
                      @else
                        <td>{{$slot['lesson']->teacher->name()}}</td>
                        <td></td>
                        <td>{{$slot['lesson']->room}}</td>
                        <td>
                          @if($slot['date'] >= $firstRegisterDate)
                            <a href="#" title="@lang('registrations.unregister')">
                              <span class="sr-only">@lang('registrations.unregister')</span>
                              <span class="glyphicon glyphicon-remove-sign register-link"></span>
                            </a>
                          @endif
                        </td>
                      @endif
                    </tr>
                    @php $prevDate = $slot['date'] @endphp
                  @endforeach
                </table>
              </div>
            @endif
          </div>
        </section>

        <section class="panel panel-default">
          <h2 class="panel-heading">Past lessons</h2>
          <div class="panel-body">
            @if(empty($documentation))
              No past lessons.
            @else
              <div class="table-responsive">
                <table class="table">
                  <thead>
                  <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Teacher</th>
                    <th>Course</th>
                    <th></th>
                  </tr>
                  </thead>
                  @php $prevDate = null @endphp
                  @foreach($documentation as $slot)
                    <tr>
                      <td @if($prevDate == $slot['date']) class="invisible" @endif>{{$slot['date']->toDateString()}}</td>
                      <td>{{$slot['start']}} &ndash; {{$slot['end']}}</td>
                      @if(!$slot['lesson'])
                        <td colspan="3" class="text-danger">No registration!</td>
                        <td>
                          @if($slot['date'] >= $firstRegisterDate)
                            register
                          @endif
                        </td>
                      @elseif($slot['lesson']->course)
                        <td>{{$slot['lesson']->teacher->name()}}</td>
                        <td>{{$slot['lesson']->course->name}}</td>
                        <td>
                          @if(empty($slot['lesson']->course->room))
                            {{$slot['lesson']->room}}
                          @else
                            {{$slot['lesson']->course->room}}
                          @endif
                        </td>
                        <td>
                          @if($slot['lesson']->course->firstLesson()->date >= $firstRegisterDate && !$slot['registration']->obligatory)
                            unregister
                          @endif
                        </td>
                      @else
                        <td>{{$slot['lesson']->teacher->name()}}</td>
                        <td></td>
                        <td>{{$slot['lesson']->room}}</td>
                        <td>
                          @if($slot['date'] >= $firstRegisterDate)
                            unregister
                          @endif
                        </td>
                      @endif
                    </tr>
                    @php $prevDate = $slot['date'] @endphp
                  @endforeach
                </table>
              </div>
            @endif
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
