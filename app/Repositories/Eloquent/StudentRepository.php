<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Absence;
use App\Models\Student;
use Illuminate\Support\Collection;

class StudentRepository implements \App\Repositories\StudentRepository {

  public function queryForGroups($groups) {
    return Student::whereIn('students.id', function($query) use ($groups) {
      $query->select('g.student_id')
          ->from('group_student as g')
          ->whereIn('g.group_id', $groups);
    });
  }

  public function queryTimetable(Student $student, $day, $number) {
    return $student->forms()->getQuery()
        ->join('timetable as t', 't.form_id', 'forms.group_id')
        ->where('t.day', $day)
        ->where('t.number', $number);
  }

  public function queryForUntisId(Collection $ids) {
    return Student::whereIn('untis_id', $ids)->with('absences');
  }

  public function deleteAbsences(Date $date) {
    Absence::where('date', $date)->delete();
  }

  public function insertAbsences(Collection $absences) {
    Absence::insert($absences->all());
  }

}
