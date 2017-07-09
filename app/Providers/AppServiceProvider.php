<?php

namespace App\Providers;

use App\Http\Redirector;
use App\Repositories\ConfigRepository;
use App\Repositories\CourseRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RegistrationRepository;
use App\Repositories\StudentRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherRepository;
use App\Services\ConfigService;
use App\Services\ConfigStorageService;
use App\Services\CourseService;
use App\Services\DocumentationService;
use App\Services\Implementation\ConfigServiceImpl;
use App\Services\Implementation\ConfigStorageServiceImpl;
use App\Services\Implementation\CourseServiceImpl;
use App\Services\Implementation\DocumentationServiceImpl;
use App\Services\Implementation\LessonServiceImpl;
use App\Services\Implementation\MiscServiceImpl;
use App\Services\Implementation\OffdayServiceImpl;
use App\Services\Implementation\RegistrationServiceImpl;
use App\Services\Implementation\StudentServiceImpl;
use App\Services\Implementation\WebUntisServiceImpl;
use App\Services\LessonService;
use App\Services\MiscService;
use App\Services\OffdayService;
use App\Services\RegistrationService;
use App\Services\StudentService;
use App\Services\WebUntisService;
use App\Validators\CourseValidator;
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
    Validator::extend('create_allowed', CourseValidator::class . '@validateCreateAllowed');
    Validator::extend('edit_allowed', CourseValidator::class . '@validateEditAllowed');
    Validator::extend('school_day', CourseValidator::class . '@validateSchoolDay');
    Validator::extend('lesson_number', CourseValidator::class . '@validateLessonNumber');
    Validator::extend('year_from', CourseValidator::class . '@validateYearFrom');
    Validator::extend('year_to', CourseValidator::class . '@validateYearTo');

    Blade::directive('json', function($expression) {
      /** @noinspection SpellCheckingInspection */
      return "<?php echo htmlspecialchars(json_encode($expression, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_NOQUOTES, 'UTF-8', false); ?>";
    });
  }

  /**
   * Register any application services.
   *
   * @return void
   */
  public function register() {
    $this->app->singleton('redirect', function($app) {
      $redirector = new Redirector($app['url']);
      if (isset($app['session.store'])) {
        $redirector->setSession($app['session.store']);
      }
      return $redirector;
    });

    // Bind repositories
    $this->app->bind(ConfigRepository::class, \App\Repositories\Eloquent\ConfigRepository::class);
    $this->app->bind(CourseRepository::class, \App\Repositories\Eloquent\CourseRepository::class);
    $this->app->bind(GroupRepository::class, \App\Repositories\Eloquent\GroupRepository::class);
    $this->app->bind(LessonRepository::class, \App\Repositories\Eloquent\LessonRepository::class);
    $this->app->bind(OffdayRepository::class, \App\Repositories\Eloquent\OffdayRepository::class);
    $this->app->bind(RegistrationRepository::class, \App\Repositories\Eloquent\RegistrationRepository::class);
    $this->app->bind(StudentRepository::class, \App\Repositories\Eloquent\StudentRepository::class);
    $this->app->bind(SubjectRepository::class, \App\Repositories\Eloquent\SubjectRepository::class);
    $this->app->bind(TeacherRepository::class, \App\Repositories\Eloquent\TeacherRepository::class);

    // Bind services
    $this->app->bind(ConfigService::class, ConfigServiceImpl::class);
    $this->app->bind(ConfigStorageService::class, ConfigStorageServiceImpl::class);
    $this->app->bind(CourseService::class, CourseServiceImpl::class);
    $this->app->bind(DocumentationService::class, DocumentationServiceImpl::class);
    $this->app->bind(LessonService::class, LessonServiceImpl::class);
    $this->app->bind(MiscService::class, MiscServiceImpl::class);
    $this->app->bind(OffdayService::class, OffdayServiceImpl::class);
    $this->app->bind(RegistrationService::class, RegistrationServiceImpl::class);
    $this->app->bind(StudentService::class, StudentServiceImpl::class);
    $this->app->bind(WebUntisService::class, WebUntisServiceImpl::class);
  }

}
