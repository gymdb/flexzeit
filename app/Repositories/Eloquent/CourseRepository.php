<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class CourseRepository implements \App\Repositories\CourseRepository {

  use RepositoryTrait;

  public function query(Teacher $teacher = null, Date $start, Date $end = null) {
    return ($teacher ? $teacher->courses()->groupBy('pivot_teacher_id', 'pivot_course_id') : Course::query())
        ->whereExists(function($q1) use ($start, $end) {
          $q1->select(DB::raw(1))
              ->from('lessons as l')
              ->whereColumn('l.course_id', 'courses.id');
          $this->inRange($q1, $start, $end, null, null, 'l.');
        })
        ->with('teacher')
        ->orderBy('first')
        ->orderBy('last')
        ->orderBy('number')
        ->groupBy('courses.id', 'courses.name')
        ->select(['courses.id', 'courses.name'])
        ->addSelect(DB::raw('(SELECT MIN(date) FROM lessons WHERE course_id = courses.id) as first'))
        ->addSelect(DB::raw('(SELECT MAX(date) FROM lessons WHERE course_id = courses.id) as last'))
        ->addSelect(DB::raw('(SELECT MIN(number) FROM lessons WHERE course_id = courses.id) as number'));
  }

  public function queryObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start, Date $end = null) {
    $query = $this->query($teacher, $start, $end)
        ->whereExists(function($exists) use ($group) {
          $exists->select(DB::raw(1))
              ->from('course_group as g')
              ->whereColumn('g.course_id', 'courses.id');
          if ($group) {
            $exists->where('g.group_id', $group->id);
          }
        });

    if ($subject) {
      $query->where('subject_id', $subject->id);
    }

    return $query->with('groups');
  }

}
