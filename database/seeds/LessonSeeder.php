<?php

use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder {

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    Teacher::all()->each(function(Teacher $t) {
      $lessons = [];
      for ($i = 0; $i < 50; $i++) {
        $lessons[] = $lesson = factory(Lesson::class)->make();
        $lessons[] = $lesson->replicate()->setAttribute('number', 2);
      }
      $t->lessons()->saveMany($lessons);
    });
  }
}
