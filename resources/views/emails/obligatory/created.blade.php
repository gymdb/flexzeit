@component('mail::message')
# @lang('mail.obligatory.created.heading', ['teacher' => $teacher->name()])

@component('mail::button', ['url' => route('teacher.courses.show', $course->id)])
@lang('mail.obligatory.created.link')
@endcomponent

@lang('courses.data.name'): {{$course->name}}

@if($course->description)
@lang('courses.data.description'):<br/>
{{$course->description}}
@endif

@if($course->subject)
@lang('courses.data.subject'): {{$course->subject->name}}
@endif

@lang('courses.data.groups'): {{$course->groups->implode('name', ', ')}}

## @lang('courses.show.lessons')

@foreach($lessons as $lesson)
@if(!$lesson->cancelled)
+ **{{$lesson->date}}**, @lang('messages.format.range', ['number' => $lesson->number])

@endif
@endforeach
@endcomponent
