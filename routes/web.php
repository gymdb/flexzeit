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

  // Documentation/feedback related pages
  Route::get('/documentation', 'DocumentationController@showDocumentation')->name('teacher.documentation');
  Route::get('/feedback', 'DocumentationController@showFeedback')->name('teacher.feedback');

  // API methods
  Route::group(['prefix' => 'api'], function() {
    // Course related API methods
    Route::get('/course/lessonsForCreate', 'CourseController@getLessonsForCreate')->middleware('params:firstDate;d|lastDate?;d|number;i');

    // Lesson related API methods
    Route::get('/lessons', 'LessonController@getLessons')->middleware('params:teacher?;i|start?;d|end?;d');

    // Documentation/Feedback related API methods
    Route::get('documentation', 'DocumentationController@getDocumentation')->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d');
    Route::get('feedback', 'DocumentationController@getFeedbackForStudent')->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d');
    Route::get('/feedback/{registration}', 'DocumentationController@getFeedbackForRegistration');
    Route::post('/feedback/{registration}', 'DocumentationController@setFeedback')->middleware('params:feedback?');

    // Registration related API methods
    Route::post('/attendance/{registration}', 'RegistrationController@setAttendance')->middleware('params:attendance;b');
    Route::post('/attendanceChecked/{lesson}', 'RegistrationController@setAttendanceChecked');

    Route::post('/unregister/lesson/{registration}', 'RegistrationController@unregisterLesson');

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
