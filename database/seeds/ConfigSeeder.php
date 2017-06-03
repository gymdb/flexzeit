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
    $config = [
      // Course creation possible until sunday one week earlier
        'course.create.day'       => 0,
        'course.create.week'      => 2,
      // Default maximum students for a lesson
        'lesson.maxstudents'      => 20,

      // Registration is possible on each day the week before
        'registration.begin.day'  => 1,
        'registration.begin.week' => 1,
        'registration.end.day'    => 0,
        'registration.end.week'   => 1,

      // School years are within 1-8
        'year.min'                => 1,
        'year.max'                => 8,

      // Year starts on monday one month ago and ends on friday in three months
        'year.start'              => Date::today()->addMonth(-1)->setToDayOfWeek(Date::MONDAY)->toDateString(),
        'year.end'                => Date::today()->addMonths(3)->setToDayOfWeek(Date::FRIDAY)->toDateString()
    ];

    $lessons = [
        1 => ['09:00', '09:30'],
        2 => ['10:00', '10:30']
    ];

    for ($d = 1; $d <= 5; $d++) {
      $config['lesson.count.' . $d] = 2;
      foreach ($lessons as $n => $times) {
        $config['lesson.start.' . $d . '.' . $n] = $times[0];
        $config['lesson.end.' . $d . '.' . $n] = $times[1];
      }
    }

    foreach ($config as $key => $value) {
      ConfigOption::create(compact('key', 'value'));
    }
  }
}
