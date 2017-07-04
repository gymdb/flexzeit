<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class LessonRepository implements \App\Repositories\LessonRepository {

  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false, Relation $relation = null) {
    $query = RepositoryHelper::inRange($relation ? $relation->getQuery() : Lesson::query(), $start, $end, $dayOfWeek, $number);
    if ($withCourse) {
      $query->has('course');
    }
    if (!$showCancelled) {
      $query->where('cancelled', false);
    }
    return $query;
  }

  public function forTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false, $withCourse = false) {
    return $this->inRange($start, $end, $dayOfWeek, $numbers, $showCancelled, $withCourse, $teacher->lessons());
  }

  public function forStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false, $withCourse = false) {
    return $this->inRange($start, $end, $dayOfWeek, $numbers, $showCancelled, $withCourse, $student->lessons());
  }

  public function buildAvailable(Student $student, Date $date, array $numbers, Teacher $teacher = null, Subject $subject = null) {
    $query = $this
        ->inRange($date, null, null, $numbers, false, false, $teacher ? $teacher->lessons() : null)
        // Must no be part of an obligatory course
        ->whereNotExists(function($query) {
          $query->select(DB::raw(1))
              ->from('course_group')
              ->whereColumn('course_group.course_id', 'lessons.course_id');
        })
        // Must be the first lesson of a course
        ->whereNotExists(function($query) {
          $query->select(DB::raw(1))
              ->from('lessons AS sub')
              ->whereColumn('sub.course_id', 'lessons.course_id')
              ->whereColumn('sub.date', '<', 'lessons.date');
        })
        ->orderBy('lessons.number')
        ->with('course', 'teacher');

    return $query;
  }

  public function forGroups(Builder $groups, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false) {
    // TODO This seems wrong
    return $this->inRange($start, $end, $dayOfWeek, $numbers, $showCancelled, $groups->lessons());
  }

}