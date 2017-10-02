<?php

namespace App\Providers;

use App\Helpers\Anacron\Schedule as AnacronSchedule;
use App\Http\Redirector;
use App\Repositories\BugReportRepository;
use App\Repositories\ConfigRepository;
use App\Repositories\CourseRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Repositories\RegistrationRepository;
use App\Repositories\StudentRepository;
use App\Repositories\RoomRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\TeacherRepository;
use App\Services\BugReportService;
use App\Services\ConfigService;
use App\Services\ConfigStorageService;
use App\Services\CourseService;
use App\Services\DocumentationService;
use App\Services\Implementation\BugReportServiceImpl;
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
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
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
    umask(0002);

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

    Collection::macro('buildDictionary', function(array $keys, $pluck = true) {
      if (empty($keys)) {
        if ($pluck) {
          return is_bool($pluck) ? $this->isNotEmpty() : $this->pluck($pluck)->first();
        }
        return $this->first();
      }

      $key = array_shift($keys);
      if ($key === 'date') {
        $key = function($item) {
          return $item->date->toDateString();
        };
      }
      return $this
          ->groupBy($key)
          ->map(function($list) use ($keys, $pluck) {
            return $list->buildDictionary($keys, $pluck);
          });
    });

    Collection::macro('dictionaryDiff', function(Collection $other) {
      return $this->flatMap(function($list, $key) use ($other) {
        if ($list instanceof Collection) {
          return $other->has($key) ? $list->dictionaryDiff($other[$key]) : $list->flatten();
        }
        return $other->has($key) ? [] : [$list];
      });
    });

    Collection::macro('nestedGet', function(array $keys) {
      $value = $this;
      foreach ($keys as $key) {
        if (!($value instanceof Collection)) {
          return null;
        }
        $value = $value->get($key);
      }
      return $value;
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

    $this->app->extend(Schedule::class, function() {
      return new AnacronSchedule();
    });

    // Bind repositories
    $this->app->singleton(BugReportRepository::class, \App\Repositories\Eloquent\BugReportRepository::class);
    $this->app->singleton(ConfigRepository::class, \App\Repositories\Eloquent\ConfigRepository::class);
    $this->app->singleton(CourseRepository::class, \App\Repositories\Eloquent\CourseRepository::class);
    $this->app->singleton(GroupRepository::class, \App\Repositories\Eloquent\GroupRepository::class);
    $this->app->singleton(LessonRepository::class, \App\Repositories\Eloquent\LessonRepository::class);
    $this->app->singleton(OffdayRepository::class, \App\Repositories\Eloquent\OffdayRepository::class);
    $this->app->singleton(RegistrationRepository::class, \App\Repositories\Eloquent\RegistrationRepository::class);
    $this->app->singleton(RoomRepository::class, \App\Repositories\Eloquent\RoomRepository::class);
    $this->app->singleton(StudentRepository::class, \App\Repositories\Eloquent\StudentRepository::class);
    $this->app->singleton(SubjectRepository::class, \App\Repositories\Eloquent\SubjectRepository::class);
    $this->app->singleton(TeacherRepository::class, \App\Repositories\Eloquent\TeacherRepository::class);

    // Bind services
    $this->app->singleton(BugReportService::class, BugReportServiceImpl::class);
    $this->app->singleton(ConfigService::class, ConfigServiceImpl::class);
    $this->app->singleton(ConfigStorageService::class, ConfigStorageServiceImpl::class);
    $this->app->singleton(CourseService::class, CourseServiceImpl::class);
    $this->app->singleton(DocumentationService::class, DocumentationServiceImpl::class);
    $this->app->singleton(LessonService::class, LessonServiceImpl::class);
    $this->app->singleton(MiscService::class, MiscServiceImpl::class);
    $this->app->singleton(OffdayService::class, OffdayServiceImpl::class);
    $this->app->singleton(RegistrationService::class, RegistrationServiceImpl::class);
    $this->app->singleton(StudentService::class, StudentServiceImpl::class);
    $this->app->singleton(WebUntisService::class, WebUntisServiceImpl::class);
  }

}
