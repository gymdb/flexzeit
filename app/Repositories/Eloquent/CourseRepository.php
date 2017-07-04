<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class CourseRepository implements \App\Repositories\CourseRepository {

  public function forTeacher(Teacher $teacher, Date $start, Date $end = null) {
    return $teacher->courses()
        ->whereExists(function($q1) use ($start, $end) {
          $q1->select(DB::raw(1))
              ->from('lessons as l')
              ->whereColumn('l.course_id', 'courses.id');
          RepositoryHelper::inRange($q1, $start, $end, null, null, 'l.');
        })
        ->with('teacher')
        ->groupBy('courses.id', 'courses.name', 'pivot_teacher_id', 'pivot_course_id')
        ->select(['courses.id', 'courses.name'])
        ->addSelect(DB::raw('(SELECT MIN(date) FROM lessons WHERE course_id = courses.id) as first'))
        ->addSelect(DB::raw('(SELECT MAX(date) FROM lessons WHERE course_id = courses.id) as last'))
        ->addSelect(DB::raw('(SELECT MIN(number) FROM lessons WHERE course_id = courses.id) as number'));
  }

}
