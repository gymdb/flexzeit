@php $success = \Illuminate\Support\Facades\Request::query('success') @endphp
@if($success !== null)
  @php $errors = \Illuminate\Support\Facades\Request::query('errors') @endphp
  @if($success && empty($errors))
    <div class="alert alert-success">
      {{$successMsg}}
    </div>
  @else
    <div class="alert alert-danger">
      {{$success ? $partialMsg : $failureMsg}}
      @if(!empty($errors))
        <p>@lang('errors.title')</p>
        <ul>
          @foreach(explode(',', $errors) as $error)
            <li>
              @if(ctype_digit($error) && $error >= 100)
                @lang('errors.http')
              @elseif(ctype_digit($error) && $error >= $errorMin && $error <= $errorMax)
                @lang('errors.' . $error)
              @else
                @lang('errors.other')
              @endif
            </li>
          @endforeach
        </ul>
      @endif
    </div>
  @endif
@endif
