<?php

use App\Helpers\Date;
use App\Models\Form;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Offday;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LessonSeeder extends Seeder {

  /**
   * Insert config options for testing the system
   *
   * @return void
   */
  public function run() {
    $start = Date::today()->addMonth(-1)->setToDayOfWeek(Date::MONDAY);
    $end = Date::today()->addMonths(3)->setToDayOfWeek(Date::FRIDAY);

    $groups = Group::all()->map(function(Group $group) {
      return $group->id;
    });

    $students = Student::all();
    $teachers = Teacher::all();

    for ($date = $start; $date <= $end; $date = $date->copy()->addDay()) {
      if ($date->dayOfWeek === 0 || $date->dayOfWeek === 6) {
        continue;
      }
      if (mt_rand(1, 100) <= 5) {
        Offday::create(['date' => $date]);
        continue;
      }

      $groups->each(function($group) use ($date) {
        if (mt_rand(1, 100) <= 2) {
          Offday::create(['date' => $date, 'group_id' => $group]);
        }
      });

      $lessons = [];
      foreach ($teachers as $teacher) {
        if ($teacher->admin || $date->dayOfWeek - 1 === $teacher->id % 5) {
          continue;
        }
        $lessons[] = factory(Lesson::class)->create(['date' => $date, 'number' => 1, 'teacher_id' => $teacher->id]);
        $lessons[] = factory(Lesson::class)->create(['date' => $date, 'number' => 2, 'teacher_id' => $teacher->id]);
      }

      if ($date <= Date::today()->addWeek()) {
        $factory = $date->isPast() ? factory(Registration::class)->states('past') : factory(Registration::class);
        $students->each(function(Student $student) use ($date, $factory, $lessons) {
          if ($student->offdays()->where('date', $date)->exists()) {
            return;
          }

          foreach ($lessons as $lesson) {
            if (mt_rand(1, 100) <= 5) {
              $factory->create(['lesson_id' => $lesson->id, 'student_id' => $student->id]);
              if (!$lesson->cancelled) {
                return;
              }
            }
          }
        });
      }
    }
  }
}
