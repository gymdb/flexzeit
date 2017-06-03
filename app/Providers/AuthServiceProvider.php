<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Teacher;
use App\Policies\CoursePolicy;
use App\Policies\LessonPolicy;
use App\Policies\RegistrationPolicy;
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
      Course::class       => CoursePolicy::class,
      Lesson::class       => LessonPolicy::class,
      Registration::class => RegistrationPolicy::class,
      Teacher::class      => TeacherPolicy::class
  ];

  /**
   * Register any authentication / authorization services.
   *
   * @return void
   */
  public function boot() {
    $this->registerPolicies();

    Gate::define('teacher', function($user) {
      return ($user instanceof Teacher);
    });

    Gate::define('student', function($user) {
      return ($user instanceof Student);
    });

    Auth::provider('multiple', function() {
      return new MultipleUserProvider();
    });
  }

}
