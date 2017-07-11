<?php

use Illuminate\Support\Facades\Route;

// Authentication related pages
Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('loginTarget');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

// Pages for teachers
Route::group(['prefix' => 'teacher', 'namespace' => 'Teacher', 'middleware' => ['auth', 'can:teacher']], function() {
  // Lesson related pages
  Route::get('/', 'LessonController@dashboard')->name('teacher.dashboard');
  Route::post('/lessons/cancel/{lesson}', 'LessonController@cancel')->name('teacher.lessons.cancel');
  Route::resource('/lessons', 'LessonController', ['as' => 'teacher', 'only' => ['index', 'show']]);

  // Course related pages
  Route::get('/courses/obligatory', 'CourseController@listObligatory')->name('teacher.courses.obligatory.list');
  Route::get('/courses/obligatory/create', 'CourseController@createObligatory')->name('teacher.courses.obligatory.create');
  Route::post('/courses/obligatory', 'CourseController@storeObligatory')->name('teacher.courses.obligatory.store');
  Route::put('/courses/obligatory/{course}', 'CourseController@updateObligatory')->name('teacher.courses.obligatory.update');
  Route::resource('/courses', 'CourseController', ['as' => 'teacher']);

  // Documentation/feedback related pages
  Route::get('/documentation', 'DocumentationController@showDocumentation')->name('teacher.documentation.list');
  Route::get('/documentation/missing', 'DocumentationController@showMissing')->name('teacher.documentation.missing');
  Route::get('/feedback', 'DocumentationController@showFeedback')->name('teacher.feedback');

  // Registration related pages
  Route::get('/registrations', 'RegistrationController@showRegistrations')->name('teacher.registrations.list');
  Route::get('/registrations/missing', 'RegistrationController@showMissing')->name('teacher.registrations.missing');
  Route::get('/registrations/absent', 'RegistrationController@showAbsent')->name('teacher.registrations.absent');

  // API methods
  Route::group(['prefix' => 'api'], function() {
    // Course related API methods
    Route::get('/courses', 'CourseController@getForTeacher')
        ->middleware('params:teacher?;i|start?;d|end?;d')
        ->name('teacher.api.courses');
    Route::get('/courses/obligatory', 'CourseController@getObligatory')
        ->middleware('params:group?;i|teacher?;i|subject?;i|start?;d|end?;d')
        ->name('teacher.api.courses.obligatory');
    Route::get('/course/lessonsForCreate', 'CourseController@getLessonsForCreate')
        ->middleware('params:firstDate;d|lastDate?;d|number;i|groups*;i');
    Route::get('/course/lessonsForEdit', 'CourseController@getLessonsForEdit')
        ->middleware('params:course;i|lastDate?;d|groups*;i');

    // Lesson related API methods
    Route::get('/lessons', 'LessonController@getForTeacher')
        ->middleware('params:teacher?;i|start?;d|end?;d')
        ->name('teacher.api.lessons');

    // Documentation/Feedback related API methods
    Route::get('/documentation', 'DocumentationController@getDocumentation')
        ->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.documentation');
    Route::get('/documentation/missing', 'DocumentationController@getMissing')
        ->middleware('params:group;i|student?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.documentation.missing');
    Route::get('/feedback', 'DocumentationController@getFeedbackForStudent')
        ->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.feedback');
    Route::get('/feedback/{registration}', 'DocumentationController@getFeedbackForRegistration');
    Route::post('/feedback/{registration}', 'DocumentationController@setFeedback')
        ->middleware('params:feedback?');

    // Registration related API methods
    Route::get('/registrations', 'RegistrationController@getForStudent')
        ->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.registrations');
    Route::get('/registrations/{date}/{number}', 'RegistrationController@getForSlot')
        ->middleware('params:student;i');
    Route::get('/registrations/missing', 'RegistrationController@getMissing')
        ->middleware('params:group;i|student?;i|start?;d|end?;d')
        ->name('teacher.api.registrations.missing');
    Route::get('/registrations/absent', 'RegistrationController@getAbsent')
        ->middleware('params:group;i|student?;i|start?;d|end?;d')
        ->name('teacher.api.registrations.absent');
    Route::post('/attendance/{registration}', 'RegistrationController@setAttendance')
        ->middleware('params:attendance;b');
    Route::post('/attendanceChecked/{lesson}', 'RegistrationController@setAttendanceChecked');
    Route::post('/register/{lesson}/{student}', 'RegistrationController@registerLesson');
    Route::post('/unregister/lesson/{registration}', 'RegistrationController@unregisterLesson');

    Route::post('/absences/refresh/{date}', 'RegistrationController@refreshAbsences');

    // Students for filter
    Route::get('students', 'FilterController@getStudents')->middleware('params:group;i');
  });
});

// Pages for students
Route::group(['prefix' => 'student', 'namespace' => 'Student', 'middleware' => ['auth', 'can:student']], function() {
  Route::get('/', 'StudentController@dashboard')->name('student.dashboard');
  Route::get('/day/{date}', 'StudentController@day')->name('student.day');

  // API methods
  Route::group(['prefix' => 'api'], function() {
    // Documentation related API methods
    Route::get('/documentation/{registration}', 'ApiController@getDocumentation');
    Route::post('/documentation/{registration}', 'ApiController@setDocumentation')
        ->middleware('params:documentation?');

    // Lesson related API methods
    Route::get('/lessons/{date}', 'ApiController@getAvailableLessons')
        ->middleware('params:subject?;i|teacher?;i')
        ->name('student.api.available');

    // Registration related API methods
    Route::post('/register/course/{course}', 'ApiController@registerCourse');
    Route::post('/register/lesson/{lesson}', 'ApiController@registerLesson');
    Route::post('/unregister/course/{course}', 'ApiController@unregisterCourse');
    Route::post('/unregister/lesson/{registration}', 'ApiController@unregisterLesson');
  });
});

if (config('app.debug')) {
  // TODO For testing purposes only, remove in production system!
  Route::get('/refresh', function($key) {
    if ($key === 'NRil5oTeb5_4t') {
      set_time_limit(1200);
      echo \Illuminate\Support\Facades\Artisan::call('migrate:refresh', ['--seed' => true]);
    } else {
      echo 'Wrong key!';
    }
  })->middleware('params:key');
}
