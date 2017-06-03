<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LessonRepository implements \App\Repositories\LessonRepository {

  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false, $withCourse = false, Relation $relation = null) {
    $query = RepositoryHelper::inRange($relation ? $relation->getQuery() : Lesson::query(), $start, $end, $dayOfWeek);
    if ($withCourse) {
      $query->has('course');
    }
    if (!is_null($numbers)) {
      $query->whereIn('number', is_scalar($numbers) ? [$numbers] : $numbers);
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

  public function forGroups(Builder $groups, Date $start, Date $end = null, $dayOfWeek = null, $numbers = null, $showCancelled = false) {
    // TODO This seems wrong
    return $this->inRange($start, $end, $dayOfWeek, $numbers, $showCancelled, $groups->lessons());
  }

}