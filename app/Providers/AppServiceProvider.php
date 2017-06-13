<?php

namespace App\Providers;

use App\Repositories\ConfigRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RegistrationRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherRepository;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\Implementation\ConfigServiceImpl;
use App\Services\Implementation\CourseServiceImpl;
use App\Services\Implementation\LessonServiceImpl;
use App\Services\Implementation\MiscServiceImpl;
use App\Services\Implementation\OffdayServiceImpl;
use App\Services\Implementation\RegistrationServiceImpl;
use App\Services\Implementation\StudentServiceImpl;
use App\Services\Implementation\TeacherServiceImpl;
use App\Services\LessonService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use App\Services\StudentService;
use App\Services\TeacherService;
use App\Validators\DateValidator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot() {
    Validator::extend('in_year', DateValidator::class . '@validateInYear');
    Validator::extend('create_allowed', DateValidator::class . '@validateCreateAllowed');
    Validator::extend('school_day', DateValidator::class . '@validateSchoolDay');

    Blade::directive('json', function($expression) {
      return "<?php echo htmlspecialchars(json_encode($expression, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_NOQUOTES, 'UTF-8', false); ?>";
    });
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register() {
    // Bind repositories
    $this->app->bind(ConfigRepository::class, \App\Repositories\Eloquent\ConfigRepository::class);
    $this->app->bind(GroupRepository::class, \App\Repositories\Eloquent\GroupRepository::class);
    $this->app->bind(LessonRepository::class, \App\Repositories\Eloquent\LessonRepository::class);
    $this->app->bind(OffdayRepository::class, \App\Repositories\Eloquent\OffdayRepository::class);
    $this->app->bind(RegistrationRepository::class, \App\Repositories\Eloquent\RegistrationRepository::class);
    $this->app->bind(SubjectRepository::class, \App\Repositories\Eloquent\SubjectRepository::class);
    $this->app->bind(TeacherRepository::class, \App\Repositories\Eloquent\TeacherRepository::class);

    // Bind services
    $this->app->bind(ConfigService::class, ConfigServiceImpl::class);
    $this->app->bind(CourseService::class, CourseServiceImpl::class);
    $this->app->bind(LessonService::class, LessonServiceImpl::class);
    $this->app->bind(MiscService::class, MiscServiceImpl::class);
    $this->app->bind(OffdayService::class, OffdayServiceImpl::class);
    $this->app->bind(RegistrationService::class, RegistrationServiceImpl::class);
    $this->app->bind(StudentService::class, StudentServiceImpl::class);
    $this->app->bind(TeacherService::class, TeacherServiceImpl::class);
  }

}
