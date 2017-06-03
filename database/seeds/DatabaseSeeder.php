<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

  protected $seeders = [ConfigSeeder::class, SubjectSeeder::class, DummySeeder::class, LessonSeeder::class];

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    foreach ($this->seeders as $seeder) {
      $this->call($seeder);
    }
  }
}
