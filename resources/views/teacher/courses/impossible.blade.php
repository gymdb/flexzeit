@extends('layouts.app')

@section('content')
  <section class="panel panel-default">
    <h2 class="panel-heading">@lang('courses.create')</h2>
    <div class="panel-body">
      <div class="alert alert-danger">
        <strong>@lang('courses.createImpossible')</strong>
      </div>
    </div>
  </section>
@endsection
