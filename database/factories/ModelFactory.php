<?php

/** @var Factory $factory */

use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Room;
use \App\Models\Student;
use App\Models\Teacher;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Lesson::class, function(Generator $faker) {
  return [
      'cancelled' => $faker->boolean(5)
  ];
});

$factory->define(Registration::class, function(Generator $faker) {
  return [
      'obligatory' => $faker->boolean(10)
  ];
});

$factory->state(Registration::class, 'past', function(Generator $faker) {
  $attendance = $faker->numberBetween(1, 100);
  return [
      'attendance'    => $attendance <= 2 ? null : $attendance >= 10,
      'documentation' => $faker->boolean(80) ? $faker->text() : null,
      'feedback'      => $faker->boolean(20) ? $faker->text() : null
  ];
});

$factory->define(Student::class, function(Generator $faker) {
  $number = $faker->unique()->numberBetween(0, 140 * 4 - 1);
  $year = [12, 13, 14, 16][intdiv($number, 140)];

  return [
      'firstname' => $faker->firstName,
      'lastname'  => $faker->lastName,
      'image'     => 'https://api.adorable.io/avatars/200/' . $faker->word . '.png',
      'untis_id'  => (40501620 * 100 + $year) * 10000 + ($number % 140) + 1
  ];
});

$factory->define(Teacher::class, function(Generator $faker) {
  return [
      'firstname' => $faker->firstName,
      'lastname'  => $faker->lastName,
      'image'     => 'https://api.adorable.io/avatars/200/' . $faker->word . '.png',
      'info'      => $faker->words(2, true)
  ];
});

$factory->define(Room::class, function(Generator $faker) {
  $number = $faker->unique()->numberBetween(1, 100);
  $year = $faker->randomElement([[1, 2], [3, 4], [5, 8], [null, null]]);
  return [
      'name'     => floor($number / 25) * 100 + ($number % 25) + 1,
      'type'     => $faker->randomElement(['Sport', 'Listening', null, null, null, null, null]),
      'capacity' => $faker->numberBetween(3, 6) * 5,
      'yearfrom' => $year[0],
      'yearto'   => $year[1]
  ];
});
