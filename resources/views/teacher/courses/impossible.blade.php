@extends('layouts.app')

@section('content')
  <main class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <section class="panel panel-default">
          <h2 class="panel-heading">@lang('courses.create')</h2>
          <div class="panel-body">
            <div class="alert alert-danger">
              <strong>@lang('courses.createImpossible')</strong>
            </div>
          </div>
        </section>
      </div>
    </div>
  </main>
@endsection
