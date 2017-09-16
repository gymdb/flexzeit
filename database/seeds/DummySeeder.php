<?php

use App\Models\Form;
use App\Models\Group;
use App\Models\Room;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummySeeder extends Seeder {

  /**
   * Insert config options for testing the system
   *
   * @return void
   */
  public function run() {

    $teacherCount = 0;

    $formList = [
        1 => ['1A', '1B', '1C', '1D', '1E'],
        2 => ['2A', '2B', '2C', '2D', '2E'],
        3 => ['3A', '3B', '3C', '3D', '3E'],
        4 => ['4A', '4B', '4C', '4D', '4E', '4F'],
        5 => ['5A', '5B', '5N'],
        6 => ['6A', '6N1', '6N2'],
        7 => ['7A', '7B', '7N'],
        8 => ['8A', '8B', '8N']
    ];

    foreach ($formList as $year => $forms) {
      $students1 = [];
      $students2 = [];

      foreach ($forms as $form) {
        $teacher = factory(Teacher::class)->create([
            'username' => 't' . ++$teacherCount,
            'password' => Hash::make('t' . $teacherCount)
        ]);

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        $group = Group::create(['name' => $form]);

        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        Form::create([
            'group_id' => $group->id,
            'year'     => $year,
            'kv_id'    => $teacher->id
        ]);

        $timetable = collect(range(1, 5))->flatMap(function($day) use ($group) {
          return collect(range(1, ($group->id % 5 === $day - 1) ? 1 : 2))->map(function($n) use ($group, $day) {
            return [
                'form_id' => $group->id,
                'day'     => $day,
                'number'  => $n
            ];
          });
        });
        DB::table('timetable')->insert($timetable->all());

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

      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
      Group::create(['name' => 'F' . $year])->students()->attach($students1);
      /** @noinspection PhpDynamicAsStaticMethodCallInspection */
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
