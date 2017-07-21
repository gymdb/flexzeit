<?php

namespace App\Providers;

use App\Models\BugReport;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Policies\BugReportPolicy;
use App\Policies\CoursePolicy;
use App\Policies\GroupPolicy;
use App\Policies\LessonPolicy;
use App\Policies\RegistrationPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy;
use App\Services\Implementation\MultipleUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider {

  /**
   * The policy mappings for the application.
   *
   * @var array
   */
  protected $policies = [
      BugReport::class    => BugReportPolicy::class,
      Course::class       => CoursePolicy::class,
      Group::class        => GroupPolicy::class,
      Lesson::class       => LessonPolicy::class,
      Registration::class => RegistrationPolicy::class,
      Student::class      => StudentPolicy::class,
      Teacher::class      => TeacherPolicy::class
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot() {
    $this->registerPolicies();

    Gate::define('teacher', function(User $user) {
      return ($user->isTeacher());
    });

    Gate::define('student', function(User $user) {
      return ($user->isStudent());
    });

    Auth::provider('multiple', function() {
      return new MultipleUserProvider();
    });
  }

}
