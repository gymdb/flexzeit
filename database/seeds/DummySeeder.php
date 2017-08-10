<?php

use App\Models\Form;
use App\Models\Group;
use App\Models\Room;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder {

  /**
   * Insert config options for testing the system
   *
   * @return void
   */
  public function run() {

    $teacherCount = 0;

    for ($year = 1; $year <= 8; $year++) {
      $students1 = [];
      $students2 = [];

      foreach (['A', 'B'] as $form) {
        $teacher = factory(Teacher::class)->create([
            'username' => 't' . ++$teacherCount,
            'password' => Hash::make('t' . $teacherCount)
        ]);

        $group = Group::create(['name' => $year . $form]);

        Form::create([
            'group_id' => $group->id,
            'year'     => $year,
            'kv_id'    => $teacher->id
        ]);

        $students = [];
        for ($i = 1; $i <= 10; $i++) {
          $student = factory(Student::class)->create([
              'username' => 's' . $group->name . $i,
              'password' => Hash::make('s' . $group->name . $i)
          ]);

          $students[] = $student->id;
          if ($i % 2) {
            $students1[] = $student->id;
          } else {
            $students2[] = $student->id;
          }
        }
        $group->students()->attach($students);
      }

      Group::create(['name' => 'F' . $year])->students()->attach($students1);
      Group::create(['name' => 'L' . $year])->students()->attach($students2);
    }

    $subjects = Subject::all()->map(function(Subject $subject) {
      return $subject->id;
    });

    $groups = Group::all()->map(function(Group $group) {
      return $group->id;
    });

    Teacher::all()->each(function(Teacher $teacher) use ($subjects, $groups) {
      $teacher->subjects()->attach($subjects->random(2));
      $teacher->groups()->attach($groups->random(5));
      if ($teacher->form) {
        $teacher->groups()->syncWithoutDetaching([$teacher->form->group_id]);
      }
    });

    factory(Teacher::class)->create([
        'username' => 'admin',
        'password' => Hash::make('admin'),
        'admin'    => true
    ]);

    factory(Room::class)
        ->times(50)
        ->create();
  }
}
