<?php

namespace App\Providers;

use App\Repositories\ConfigRepository;
use App\Repositories\GroupRepository;
use App\Repositories\LessonRepository;
use App\Repositories\OffdayRepository;
use App\Services\ConfigService;
use App\Services\CourseService;
use App\Services\LessonService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot() {
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

    // Services
    $this->app->bind(ConfigService::class, \App\Services\Implementation\ConfigService::class);
    $this->app->bind(CourseService::class, \App\Services\Implementation\CourseService::class);
    $this->app->bind(LessonService::class, \App\Services\Implementation\LessonService::class);
  }

}
