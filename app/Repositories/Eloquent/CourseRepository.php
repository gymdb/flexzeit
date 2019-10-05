<?php

namespace App\Repositories\Eloquent;

use App\Helpers\DateConstraints;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;

class CourseRepository implements \App\Repositories\CourseRepository {

  use RepositoryTrait;

  public function query(Teacher $teacher = null, DateConstraints $constraints) {
    return Course::query()
        ->whereIn('id', function($in) use ($constraints, $teacher) {
          $in->select('l.course_id')
              ->whereNotNull('l.course_id')
              ->from('lessons as l');
          $this->inRange($in, $constraints, 'l.');
          if ($teacher) {
            $in->where('l.teacher_id', $teacher->id);
          }
        })
        ->orderBy('first')
        ->orderBy('last')
        ->orderBy('number')
        ->orderBy('name')
        ->select(['id', 'name', 'maxstudents', 'category'])
        ->addSelect(DB::raw('(SELECT MIN(date) FROM lessons WHERE course_id = courses.id) as first'))
        ->addSelect(DB::raw('(SELECT MAX(date) FROM lessons WHERE course_id = courses.id) as last'))
        ->addSelect(DB::raw('(SELECT MIN(number) FROM lessons WHERE course_id = courses.id) as number'));
  }

  public function queryObligatory(Group $group = null, Teacher $teacher = null, Subject $subject = null, DateConstraints $constraints) {
    $query = $this->query($teacher, $constraints)
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

  public function queryAvailable(Student $student, Teacher $teacher = null, DateConstraints $constraints) {
    $query = Course::doesntHave('groups')
        ->join('lessons as l', function($join) use ($constraints) {
          $join->on('l.course_id', 'courses.id')
              ->whereNotExists(function($exists) {
                $exists->select(DB::raw(1))
                    ->from('lessons as sub')
                    ->where('sub.cancelled', false)
                    ->whereColumn('sub.course_id', 'courses.id')
                    ->whereColumn('sub.date', '<', 'l.date');
              })
              ->where('l.cancelled', false);
          $this->inRange($join, $constraints, 'l.');
        })
        ->select(['courses.id', 'name', 'description', 'maxstudents', 'l.date', 'l.number', 'category'])
        ->addSelect(DB::raw('(SELECT MAX(date) FROM lessons WHERE course_id = courses.id) as last'));

    // The student must not have offdays for all lessons of the course
    $query->whereNotExists(function($exists) use ($student) {
      $exists->select(DB::raw(1))
          ->from('lessons as d')
          ->where('d.cancelled', false)
          ->whereColumn('d.course_id', 'courses.id')
          ->where(function($sub) use ($student) {
            $this->restrictToTimetable($sub, $student, true);
            $this->excludeOffdays($sub, $student, true);
          });
    });

    // Only show lessons with free spots
    $query->where(function($or) {
      $sub = $this->getParticipantsQuery(true);
      $or->whereNull('maxstudents')
          ->orWhereRaw("({$sub->toSql()}) < maxstudents")
          ->addBinding($sub->getBindings());
    });

    // Limit to allowed years for course
    $year = $student->forms()->take(1)->pluck('year')->first();
    $query->where(function($sub) use ($year) {
      $sub->whereNull('courses.yearfrom');
      if ($year) {
        $sub->orWhere('courses.yearfrom', '<=', $year);
      }
    })->where(function($sub) use ($year) {
      $sub->whereNull('courses.yearto');
      if ($year) {
        $sub->orWhere('courses.yearto', '>=', $year);
      }
    });

    $query->orderBy('l.date')->orderBy('l.number');
    return $query;
  }

  public function addParticipants($query) {
    return $query->selectSub($this->getParticipantsQuery(true), 'participants');
  }
}
