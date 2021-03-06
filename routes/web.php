<?php

use Illuminate\Support\Facades\Route;

// Authentication related pages
Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('/login', 'Auth\LoginController@login')->name('loginTarget');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

/** @noinspection PhpParamsInspection, PhpMethodParametersCountMismatchInspection */
Route::group(['prefix' => 'api'], function() {
  // Bug reports related API methods
  Route::post('/bugReport', 'Teacher\BugReportController@createBugReport')
      ->middleware(['auth', 'params:text']);
});

// Pages for teachers
/** @noinspection PhpParamsInspection, PhpMethodParametersCountMismatchInspection */
Route::group(['prefix' => 'teacher', 'namespace' => 'Teacher', 'middleware' => ['auth', 'can:teacher']], function() {
  // Lesson related pages
  Route::get('/', 'LessonController@dashboard')->name('teacher.dashboard');
  Route::post('/lessons/cancel/{lesson}', 'LessonController@cancel')->name('teacher.lessons.cancel');
  Route::post('/lessons/reinstate/{lesson}', 'LessonController@reinstate')->name('teacher.lessons.reinstate');
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
  Route::get('/registrations/missingSportsRegistration', 'RegistrationController@showMissingSportsRegistration')->name('teacher.registrations.missingSportsRegistration');
  Route::get('/registrations/absent', 'RegistrationController@showAbsent')->name('teacher.registrations.absent');
  Route::get('/registrations/byteacher', 'RegistrationController@showByTeacher')->name('teacher.registrations.byteacher');

  // Bug report related pages
  Route::get('/bugreports', 'BugReportController@showBugReports')->name('teacher.bugreports.list');

  // API methods
  /** @noinspection PhpParamsInspection, PhpMethodParametersCountMismatchInspection */
  Route::group(['prefix' => 'api'], function() {
    // Course related API methods
    Route::get('/courses', 'CourseController@getForTeacher')
        ->middleware('params:teacher?;i|start?;d|end?;d')
        ->name('teacher.api.courses');
    Route::get('/courses/obligatory', 'CourseController@getObligatory')
        ->middleware('params:group?;i|teacher?;i|subject?;i|start?;d|end?;d')
        ->name('teacher.api.courses.obligatory');
    Route::get('/course/dataForCreate', 'CourseController@getDataForCreate')
        ->middleware('params:firstDate;d|lastDate?;d|frequency?;i|number;i|groups*;i|teacher?;i');
    Route::get('/course/dataForEdit', 'CourseController@getDataForEdit')
        ->middleware('params:course;i|lastDate?;d|groups*;i');

    // Lesson related API methods
    Route::get('/lessons', 'LessonController@getForTeacher')
        ->middleware('params:teacher?;i|start?;d|end?;d|number?;i')
        ->name('teacher.api.lessons');
    Route::get('/lessons/substitute/{lesson}', 'LessonController@getSubstituteInformation')
        ->middleware('params:teacher;i');
    Route::post('/lessons/substitute/{lesson}/{teacher}', 'LessonController@substitute');

    // Documentation/Feedback related API methods
    Route::get('/documentation', 'DocumentationController@getDocumentation')
        ->middleware('params:student;i|subject?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.documentation');
    Route::get('/documentation/missing', 'DocumentationController@getMissing')
        ->middleware('params:group;i|student?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.documentation.missing');
    Route::get('/feedback', 'DocumentationController@getFeedbackForStudent')
        ->middleware('params:student?;i|subject?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.feedback');
    Route::get('/feedback/{registration}', 'DocumentationController@getFeedbackForRegistration');
    Route::post('/feedback/{registration}', 'DocumentationController@setFeedback')
        ->middleware('params:feedback?');

    // Registration related API methods
    Route::get('/registrations', 'RegistrationController@getForStudent')
        ->middleware('params:group;i|student?;i|subject?;i|teacher?;i|start?;d|end?;d')
        ->name('teacher.api.registrations');
    Route::get('/registrations/warnings/course/{course}', 'RegistrationController@getWarningsForCourse')
        ->middleware('params:student;i');
    Route::get('/registrations/warnings/lesson/{lesson}', 'RegistrationController@getWarningsForLesson')
        ->middleware('params:student;i');
    Route::get('/registrations/missing', 'RegistrationController@getMissing')
        ->middleware('params:group?;i|student?;i|start?;d|end?;d')
        ->name('teacher.api.registrations.missing');
    Route::get('/registrations/missingSportsRegistration', 'RegistrationController@getMissingSportsRegistration')
          ->middleware('params:group?;i|start?;d|end?;d')
          ->name('teacher.api.registrations.missingSportsRegistration');
    Route::get('/registrations/absent', 'RegistrationController@getAbsent')
        ->middleware('params:group;i|student?;i|start?;d|end?;d')
        ->name('teacher.api.registrations.absent');
    Route::get('/registrations/byteacher', 'RegistrationController@getByTeacher')
        ->middleware('params:group?;i|student?;i|start?;d|end?;d')
        ->name('teacher.api.registrations.byteacher');
    Route::post('/attendance/{registration}', 'RegistrationController@setAttendance')
        ->middleware('params:attendance;b');
    Route::post('/attendanceChecked/{lesson}', 'RegistrationController@setAttendanceChecked');
    Route::post('/register/{lesson}/{student}', 'RegistrationController@registerLesson');
    Route::post('/register/lesson/{lesson}/{student}', 'RegistrationController@registerLesson');
    Route::post('/register/course/{course}/{student}', 'RegistrationController@registerCourse');
    Route::post('/unregister/lesson/{registration}', 'RegistrationController@unregisterLesson');
    Route::post('/unregister/course/{course}/{student}', 'RegistrationController@unregisterCourse');

    Route::post('/absences/refresh/{date}', 'RegistrationController@refreshAbsences');

    // Students for filter
    Route::get('students', 'FilterController@getStudents')->middleware('params:group;i');

    // Bug report related API methods
    Route::get('/bugreports', 'BugReportController@getBugReports')
        ->middleware('params:start?;d|end?;d|showTrashed?;b')
        ->name('teacher.api.bugreports');

    Route::post('/bugreports/trash/{report}', 'BugReportController@trash');
    Route::post('/bugreports/restore/{report}', 'BugReportController@restore');
  });
});

// Pages for students
/** @noinspection PhpParamsInspection, PhpMethodParametersCountMismatchInspection */
Route::group(['prefix' => 'student', 'namespace' => 'Student', 'middleware' => ['auth', 'can:student']], function() {
  Route::get('/', 'StudentController@dashboard')->name('student.dashboard');
  Route::get('/day/{date}', 'StudentController@day')->name('student.day');
  Route::get('/courses', 'StudentController@courses')->name('student.courses');

  // API methods
  /** @noinspection PhpParamsInspection, PhpMethodParametersCountMismatchInspection */
  Route::group(['prefix' => 'api'], function() {
    // Documentation related API methods
    Route::get('/documentation/{registration}', 'ApiController@getDocumentation');
    Route::post('/documentation/{registration}', 'ApiController@setDocumentation')
        ->middleware('params:documentation?');

    // Lesson related API methods
    Route::get('/lessons/{date}', 'ApiController@getAvailableLessons')
        ->middleware('params:subject?;i|teacher?;i|type?;s')
        ->name('student.api.available');

    // Registration related API methods
    Route::post('/register/course/{course}', 'ApiController@registerCourse');
    Route::post('/register/lesson/{lesson}', 'ApiController@registerLesson');
    Route::post('/unregister/course/{course}', 'ApiController@unregisterCourse');
    Route::post('/unregister/lesson/{registration}', 'ApiController@unregisterLesson');

    // Course related API methods
    Route::get('/courses', 'ApiController@getCourses')
        ->middleware('params:teacher?;i|start?;d|end?;d')
        ->name('student.api.courses');
  });
});
