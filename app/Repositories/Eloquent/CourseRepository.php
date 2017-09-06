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
    return Course::query()
        ->whereIn('id', function($in) use ($start, $end, $teacher) {
          $in->select('l.course_id')
              ->whereNotNull('l.course_id')
              ->from('lessons as l');
          $this->inRange($in, $start, $end, null, null, 'l.');
          if ($teacher) {
            $in->where('l.teacher_id', $teacher->id);
          }
        })
        ->orderBy('first')
        ->orderBy('last')
        ->orderBy('number')
        ->orderBy('name')
        ->select(['id', 'name', 'maxstudents'])
        ->addSelect(DB::raw('(SELECT MIN(date) FROM lessons WHERE course_id = courses.id) as first'))
        ->addSelect(DB::raw('(SELECT MAX(date) FROM lessons WHERE course_id = courses.id) as last'))
        ->addSelect(DB::raw('(SELECT MIN(number) FROM lessons WHERE course_id = courses.id) as number'));
  }

  public function queryObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, Date $start, Date $end = null) {
    $query = $this->query($teacher, $start, $end)
        ->whereIn('courses.id', function($in) use ($group) {
          $in->select('g.course_id')
              ->from('course_group as g')
              ->distinct();
          if ($group) {
            $in->where('g.group_id', $group->id);
          }
        });

    if ($subject) {
      $query->where('subject_id', $subject->id);
    }

    return $query;
  }

  public function addParticipants($query) {
    return $query->selectSub($this->getParticipantsQuery(true), 'participants');
  }

}
