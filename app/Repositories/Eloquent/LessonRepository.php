<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class LessonRepository implements \App\Repositories\LessonRepository {

  use RepositoryTrait;

  /**
   * Build a query for all lessons within a given range
   *
   * @param Date $start Start date
   * @param Date|null $end Optional end date (start day only if empty)
   * @param int|null $dayOfWeek Only show dates on the given day of week
   * @param int[]|int|null $number Only show lessons with these numbers
   * @param bool $showCancelled Also include cancelled lessons in the result
   * @param bool $withCourse Only show lessons with an assigned course
   * @param Relation|null $relation Relation to run the query on
   * @return Builder
   */
  private function queryInRange(Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false, Relation $relation = null) {
    $query = $this->inRange($relation ? $relation->getQuery() : Lesson::query(), $start, $end, $dayOfWeek, $number);
    if ($withCourse) {
      $query->whereNotNull('course_id');
    }
    if (!$showCancelled) {
      $query->where('cancelled', false);
    }
    return $query;
  }

  public function queryForTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    return $this->queryInRange($start, $end, $dayOfWeek, $number, $showCancelled, $withCourse, $teacher->lessons());
  }

  public function queryForStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    return $this->queryInRange($start, $end, $dayOfWeek, $number, $showCancelled, $withCourse, $student->lessons());
  }

  public function queryAvailable(Student $student, Date $date, array $numbers, Teacher $teacher = null, Subject $subject = null) {
    $query = $this->queryInRange($date, null, null, $numbers, false, false, $teacher ? $teacher->lessons() : null)
        // Must no be part of an obligatory course
        ->whereNotExists(function($exists) {
          $exists->select(DB::raw(1))
              ->from('course_group')
              ->whereColumn('course_group.course_id', 'lessons.course_id');
        })
        // Must be the first lesson of a course
        ->whereNotExists(function($exists) {
          $exists->select(DB::raw(1))
              ->from('lessons AS sub')
              ->whereColumn('sub.course_id', 'lessons.course_id')
              ->whereColumn('sub.date', '<', 'lessons.date');
        })
        ->orderBy('lessons.number')
        ->with('course', 'teacher');

    if ($subject) {
      $query->whereExists(function($exists) use ($subject) {
        $exists->select(DB::raw(1))
            ->from('subject_teacher as s')
            ->whereColumn('s.teacher_id', 'lessons.teacher_id')
            ->where('s.subject_id', $subject->id);
      });
    }

    return $query;
  }

  public function queryForGroups(array $groups, Date $start, Date $end = null, $dayOfWeek = null, $number = null, Course $exclude = null) {
    $query = $this->queryInRange($start, $end, $dayOfWeek, $number)
        ->whereExists(function($exists) use ($groups) {
          $exists->select(DB::raw(1))
              ->from('course_group as g')
              ->whereColumn('g.course_id', 'lessons.course_id')
              ->whereIn('g.group_id', $groups);
        });
    if ($exclude) {
      $query->where('lessons.course_id', '!=', $exclude->id);
    }

    return $query->with('course', 'course.groups');
  }

}