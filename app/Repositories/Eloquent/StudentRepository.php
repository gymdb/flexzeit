<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Absence;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentRepository implements \App\Repositories\StudentRepository {

  public function queryForGroups($groups) {
    return Student::whereExists(function($query) use ($groups) {
      $query->select(DB::raw(1))
          ->from('group_student')
          ->whereColumn('student_id', 'students.id')
          ->whereIn('group_id', $groups);
    });
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
