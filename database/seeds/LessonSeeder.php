<?php

use App\Helpers\Date;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Offday;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

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

      for ($i = 1; $i <= 2; $i++) {
        $groups->each(function($group) use ($date, $i) {
          if (mt_rand(1, 100) <= 2) {
            Offday::create(['date' => $date, 'number' => $i, 'group_id' => $group]);
          }
        });

        $lessons = [];
        foreach ($teachers as $teacher) {
          if ($teacher->admin || $date->dayOfWeek - 1 === $teacher->id % 5) {
            continue;
          }
          $lessons[] = factory(Lesson::class)->create(['date' => $date, 'number' => $i, 'teacher_id' => $teacher->id]);
        }

        if ($date <= Date::today()->addWeek()) {
          $factory = $date->isPast() ? factory(Registration::class)->states('past') : factory(Registration::class);
          $students->each(function(Student $student) use ($date, $i, $factory, $lessons) {
            if ($student->offdays()->where('date', $date)->where('number', $i)->exists()) {
              return;
            }

            foreach ($lessons as $lesson) {
              if (mt_rand(1, 100) <= 20) {
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
}
