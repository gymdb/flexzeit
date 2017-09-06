<?php

namespace App\Repositories\Eloquent;

use App\Models\Group;
use Illuminate\Support\Facades\DB;

class GroupRepository implements \App\Repositories\GroupRepository {

  public function query() {
    return Group::query();
  }

  public function queryTimetable(array $groups, $day, $number) {
    return Group::whereIn('id', $groups)
        ->whereExists(function($exists) use ($day, $number) {
          $exists->select(DB::raw(1))
              ->from('group_student as g1')
              ->whereColumn('g1.group_id', 'groups.id')
              ->whereNotIn('g1.student_id', function($in) use ($day, $number) {
                $in->select('g2.student_id')
                    ->from('timetable as t')
                    ->join('group_student as g2', function($join) {
                      $join->on('g2.group_id', 't.form_id');
                    })
                    ->where('t.day', $day)
                    ->where('t.number', $number);
              });
        });
  }

}
