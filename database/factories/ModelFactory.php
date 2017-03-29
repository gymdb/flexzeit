<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Faker\Generator;

$factory->define(User::class, function(Generator $faker) {
  static $password;

  return [
      'name'           => $faker->name,
      'email'          => $faker->unique()->safeEmail,
      'password'       => $password ?: $password = bcrypt('secret'),
      'remember_token' => str_random(10),
  ];
});

$factory->define(Lesson::class, function(Generator $faker) {
  $date = Carbon::createFromTimestampUTC($faker->unique()->numberBetween(17100, 17400) * 86400);
  return [
      'date'   => $date,
      'number' => 1,
      'room'   => $faker->words(1, true)
  ];
});
