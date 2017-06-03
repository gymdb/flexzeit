<?php

/** @var Factory $factory */
use App\Models\Lesson;
use App\Models\Registration;
use \App\Models\Student;
use App\Models\Teacher;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Lesson::class, function(Generator $faker) {
  return [
      'room'      => $faker->numberBetween(0, 3) * 100 + $faker->numberBetween(1, 25),
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
      'attendance' => $attendance <= 5 ? null : $attendance >= 20,
      'documentation'  => $faker->boolean(80) ? $faker->text() : null,
      'feedback'  => $faker->boolean(20) ? $faker->text() : null
  ];
});

$factory->define(Student::class, function(Generator $faker) {
  return [
      'firstname' => $faker->firstName,
      'lastname'  => $faker->lastName,
      'image'     => 'https://api.adorable.io/avatars/200/' . $faker->word . '.png'
  ];
});

$factory->define(Teacher::class, function(Generator $faker) {
  return [
      'firstname' => $faker->firstName,
      'lastname'  => $faker->lastName,
      'info'      => $faker->words(2, true)
  ];
});
