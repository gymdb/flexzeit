@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('errors.heading')</h2>
    <div class="panel-body">
      <div class="alert alert-danger">
        <p><strong>@lang('errors.title')</strong></p>
        <p>
          @if(Illuminate\Support\Facades\Lang::has("errors.$status"))
            @lang("errors.$status")
          @elseif($status < 500)
            @lang('errors.http', ['code' => $status])
          @elseif($status < 600)
            @lang('errors.5xx', ['code' => $status])
          @else
            @lang('errors.unknown', ['code' => $status])
          @endif
        </p>
        @if($message)
          <p>@lang('errors.message'): {{$message}}</p>
        @endif
      </div>
    </div>
  </section>
@endsection
