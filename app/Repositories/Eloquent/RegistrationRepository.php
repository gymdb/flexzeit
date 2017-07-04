<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class RegistrationRepository implements \App\Repositories\RegistrationRepository {

  public function forStudent($student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null) {
    $query = $student->registrations()->getQuery()
        ->join('lessons as l', 'l.id', 'lesson_id');
    $query = RepositoryHelper::inRange($query, $start, $end, null, $number, 'l.');

    if (!$showCancelled) {
      $query->where('l.cancelled', false);
    }
    if ($teacher) {
      $query->where('l.teacher_id', $teacher->id);
    }
    if ($subject) {
      $query->where(function($q1) use ($subject) {
        $q1->whereExists(function($q2) use ($subject) {
          $q2->select(DB::raw(1))
              ->from('courses as c')
              ->whereColumn('c.id', 'l.course_id')
              ->where('c.subject_id', $subject->id);
        });
        $q1->orWhereExists(function($q2) use ($subject) {
          $q2->select(DB::raw(1))
              ->from('subject_teacher as s')
              ->whereColumn('s.teacher_id', 'l.teacher_id')
              ->where('s.subject_id', $subject->id);
        });
      });
    }
    return $query;
  }

  public function getMissing(Group $group, Student $student = null, Date $start, Date $end) {
    return ($student ? Student::whereKey($student->id) : $group->students())
        ->crossJoin(DB::raw('(SELECT DISTINCT date, number FROM lessons) as d'))
        ->whereBetween('d.date', [$start, $end])
        ->whereNotExists(function($query) {
          $query->select(DB::raw(1))
              ->from('registrations as r')
              ->join('lessons as l', 'l.id', 'r.lesson_id')
              ->whereColumn('r.student_id', 'students.id')
              ->whereColumn('l.date', 'd.date')
              ->whereColumn('l.number', 'd.number')
              ->where('l.cancelled', false);
        })
        ->whereNotExists(function($query) {
          $query->select(DB::raw(1))
              ->from('offdays as o')
              ->join('group_student as g', function($join) {
                $join->on('g.group_id', 'o.group_id')->orWhereNull('o.group_id');
              })
              ->whereColumn('g.student_id', 'students.id')
              ->whereColumn('o.date', 'd.date')
              ->whereColumn('o.number', 'd.number');
        })
        ->whereNotExists(function($query) {
          $query->select(DB::raw(1))
              ->from('absences as a')
              ->whereColumn('a.student_id', 'students.id')
              ->whereColumn('a.date', 'd.date')
              ->whereColumn('a.number', 'd.number');
        });
  }

  public function getSlots(Student $student, Date $start, Date $end = null) {
    $slotQuery = $this->getSlotQuery($start, $end);
    $joinQuery = DB::table('lessons as l')
        ->join('registrations as r', function($join) use ($student) {
          $join->on('r.lesson_id', 'l.id')->where('r.student_id', $student->id)->where('l.cancelled', false);
        });
    $joinSql = preg_replace('/^select +\* +from +/i', '', $joinQuery->toSql());

    $query = Lesson::query()
        ->from(DB::raw("({$slotQuery->toSql()}) as d"))
        ->leftJoin(DB::raw("({$joinSql})"), function($join) use ($student) {
          $join->on('d.date', 'l.date')->on('d.number', 'l.number');
        })
        ->whereNotExists(function($query) use ($student) {
          $query->select(DB::raw(1))
              ->from('offdays as o')
              ->join('group_student as g', function($join) {
                $join->on('g.group_id', 'o.group_id')->orWhereNull('o.group_id');
              })
              ->where('g.student_id', $student->id)
              ->whereColumn('o.date', 'd.date')
              ->whereColumn('o.number', 'd.number');
        })
        ->orderBy('d.date')
        ->orderBy('d.number');
    $query->addBinding($slotQuery->getBindings(), 'join');
    $query->addBinding($joinQuery->getBindings(), 'join');

    return $query;
  }

  private function getSlotQuery(Date $start, Date $end = null) {
    return RepositoryHelper::inRange(DB::table('lessons')->distinct()->select(['date', 'number']), $start, $end);
  }

}
