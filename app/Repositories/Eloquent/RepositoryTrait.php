<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Helpers\DateRange;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait RepositoryTrait {

  /**
   * Restrict a query to a date, range of dates or day of week within a range of dates
   *
   * @param Builder $query
   * @param Date $from
   * @param Date|null $to
   * @param int|null $dayOfWeek
   * @param int|int[]|null $number
   * @param string $table
   * @return Builder
   */
  private function inRange($query, Date $from, Date $to = null, $dayOfWeek = null, $number = null, $table = '') {
    if (!is_null($number) && empty($this->noNumber)) {
      $query->where(function($query) use ($number, $table) {
        $query->whereIn($table . 'number', is_scalar($number) ? [$number] : $number)
            ->orWhereNull($table . 'number');
      });
    }

    $query->orderBy($table . 'date');
    if (empty($this->noNumber)) {
      $query->orderBy($table . 'number');
    }

    if (is_null($to)) {
      return $query->where($table . 'date', $from);
    }

    return is_null($dayOfWeek)
        ? $query->whereBetween($table . 'date', [$from, $to])
        : $query->whereIn($table . 'date', DateRange::getDates($from, $to, $dayOfWeek));
  }

  /**
   * Restrict the slot results to ones in the timetable of the student
   *
   * @param Builder $query
   * @param Student|null $student
   * @param bool $invert
   */
  private function restrictToTimetable($query, Student $student = null, $invert = false) {
    // Only show slots where the student actually has flex lessons scheduled
    $query->whereExists(function($exists) use ($student) {
      $exists->select(DB::raw(1))
          ->from('timetable as t')
          ->join('group_student as g', function($join) {
            $join->on('g.group_id', 't.form_id');
          })
          ->where('t.day', DB::raw('dow(d.date)'))
          ->whereColumn('t.number', 'd.number');

      if ($student) {
        $exists->where('g.student_id', $student->id);
      } else {
        $exists->whereColumn('g.student_id', 'students.id');
      }
    }, $invert ? 'or' : 'and', $invert);
  }

  /**
   * Exclude slots that already have registrations
   *
   * @param Builder $query
   * @param Student|null $student
   * @param bool $invert
   */
  private function excludeExistingRegistrations($query, Student $student = null, $invert = false) {
    // Don't show slots where the student has a non-cancelled registration
    $query->whereExists(function($exists) use ($student) {
      $exists->select(DB::raw(1))
          ->from('registrations as r')
          ->join('lessons as l', 'l.id', 'r.lesson_id')
          ->whereColumn('l.date', 'd.date')
          ->whereColumn('l.number', 'd.number')
          ->where('l.cancelled', false);

      if ($student) {
        $exists->where('r.student_id', $student->id);
      } else {
        $exists->whereColumn('r.student_id', 'students.id');
      }
    }, $invert ? 'or' : 'and', !$invert);
  }

  /**
   * Exclude offdays from the result
   *
   * @param Builder $query
   * @param Student|null $student
   * @param bool $invert
   */
  private function excludeOffdays($query, Student $student = null, $invert = false) {
    $query->whereExists(function($exists) use ($student) {
      $exists->select(DB::raw(1))
          ->from('offdays as o')
          ->join('group_student as g', function($join) {
            $join->on('g.group_id', 'o.group_id')->orWhereNull('o.group_id');
          })
          ->whereColumn('o.date', 'd.date')
          ->whereColumn('o.number', 'd.number');

      if ($student) {
        $exists->where('g.student_id', $student->id);
      } else {
        $exists->whereColumn('g.student_id', 'students.id');
      }
    }, $invert ? 'or' : 'and', !$invert);
  }

  private function getParticipantsQuery($forCourseList = false) {
    $query = Registration::whereIn('registrations.lesson_id', function($in) use ($forCourseList) {
      $in->select('l.id')
          ->from('lessons as l')
          ->where('l.cancelled', false);
      if ($forCourseList) {
        $in->whereColumn('l.course_id', 'courses.id');
      } else {
        $in->where(function($sub) {
          $sub->whereColumn('l.id', 'lessons.id')
              ->orWhereColumn('l.course_id', 'lessons.course_id');
        });
      }
    })->distinct()->getQuery();
    $query->aggregate = ['function' => 'count', 'columns' => ['student_id']];
    return $query;
  }

}
