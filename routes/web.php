<?php

// Authentication related pages
Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('loginTarget');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

// Pages for teachers
Route::group(['prefix' => 'teacher', 'namespace' => 'Teacher', 'middleware' => ['auth', 'can:teacher']], function() {
  // Lesson related pages
  Route::get('/', 'LessonController@dashboard')->name('teacher.dashboard');
  Route::resource('/lessons', 'LessonController', ['as' => 'teacher', 'only' => ['index', 'show']]);

  // Course related pages
  Route::resource('/courses', 'CourseController', ['as' => 'teacher']);

  // Registration related pages
  Route::get('/documentation', 'RegistrationController@showDocumentation')->name('teacher.documentation');

  // API methods
  Route::group(['prefix' => 'api'], function() {
    // Course related API methods
    Route::get('/course/lessonsForCreate', 'CourseController@getLessonsForCreate')->middleware('params:firstDate;d|lastDate?;d|number;i');

    // Lesson related API methods
    Route::get('/lessons', 'LessonController@getLessons')->middleware('params:teacher?;i|start?;d|end?;d');

    // Registration related API methods
    Route::get('/feedback/{registration}', 'RegistrationController@getFeedback');
    Route::post('/feedback/{registration}', 'RegistrationController@setFeedback')->middleware('params:feedback?');

    Route::post('/attendance/{registration}', 'RegistrationController@setAttendance')->middleware('params:attendance;b');
    Route::post('/attendanceChecked/{lesson}', 'RegistrationController@setAttendanceChecked');

    Route::post('/unregister/lesson/{registration}', 'RegistrationController@unregisterLesson');

    Route::get('documentation', 'RegistrationController@getDocumentation')->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d');

    // Students for filter
    Route::get('students', 'FilterController@getStudents')->middleware('params:group;i');
  });
});

// Pages for students
Route::group(['prefix' => 'student', 'namespace' => 'Student', 'middleware' => ['auth', 'can:student']], function() {
  Route::get('/', 'StudentController@dashboard')->name('student.dashboard');
  Route::get('/day/{date}', 'StudentController@day')->name('student.day');
  Route::post('/register/course/{course}', 'JsonController@registerCourse');
  Route::post('/register/lesson/{lesson}', 'JsonController@registerLesson');
});
