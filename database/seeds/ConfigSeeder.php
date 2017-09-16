<?php

use App\Helpers\Date;
use App\Models\ConfigOption;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder {

  /**
   * Insert config options for testing the system
   *
   * @return void
   */
  public function run() {
    $times = [
        1 => ['start' => '08:50', 'end' => '09:33'],
        2 => ['start' => '11:10', 'end' => '11:53']
    ];
    $lessons = [];

    for ($d = 1; $d <= 5; $d++) {
      $lessons[$d] = [];
      foreach ($times as $n => $time) {
        $lessons[$d][$n] = $time;
      }
    }

    $config = [
      // Lesson count and times
        'lessons'                 => $lessons,

      // Course creation possible until wednesday one week earlier
        'course.create.day'       => 3,
        'course.create.week'      => 1,

      // Registration is possible starting on thursday one week earlier until two days before
        'registration.begin.day'  => 3,
        'registration.begin.week' => 1,
        'registration.end.day'    => 2,
        'registration.end.week'   => 0,

      // Documentation is possible for a week
        'documentation.day'       => 7,
        'documentation.week'      => 0,

      // School years are within 1-8
        'year.min'                => 1,
        'year.max'                => 8,

      // Year starts on monday one month ago and ends on friday in three months
        'year.start'              => Date::today()->addMonth(-1)->setToDayOfWeek(Date::MONDAY)->toDateString(),
        'year.end'                => Date::today()->addMonths(3)->setToDayOfWeek(Date::FRIDAY)->toDateString(),

      // Mail notifications to test address (just send to itself for testing)
        'notification.recipients' => [config('mail.from.address')]
    ];

    foreach ($config as $key => $value) {
      ConfigOption::create(compact('key', 'value'));
    }
  }
}
