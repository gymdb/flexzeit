<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class RegistrationRepository implements \App\Repositories\RegistrationRepository {

  public function queryForStudent($student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null) {
    $query = $student->registrations()
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

    return $query->with('lesson', 'lesson.teacher');
  }

  public function queryDocumentation($student, Date $start, Date $end = null, Teacher $teacher = null, Subject $subject = null) {
    $query = $this->queryForStudent($student, $start, $end, null, false, $teacher, $subject)
        ->where(function($q1) {
          $q1->where('attendance', true)->orWhereNull('attendance');
        });
    if (!$student) {
      $query->join('students', 'students.id', 'registrations.student_id')
          ->orderBy('students.lastname')
          ->orderBy('students.firstname');
    }

    return $query->with('student');
  }

  public function queryMissing(Group $group, Student $student = null, Date $start, Date $end) {
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
        })
        ->orderBy('date')
        ->orderBy('lastname')
        ->orderBy('firstname')
        ->orderBy('number');
  }

  public function querySlots(Student $student, Date $start, Date $end = null) {
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

    return $query->with('teacher', 'course');
  }

  public function queryWithExcused(Student $student, Date $start, Date $end = null, $number = null, $showCancelled = false, Teacher $teacher = null, Subject $subject = null) {
    $query = $this->queryForStudent($student, $start, $end, $number, $showCancelled, $teacher, $subject);
    return $this->addExcused($query);
  }

  public function queryAbsent($student, Date $start, Date $end = null) {
    $query = $this->queryForStudent($student, $start, $end)
        ->where(function($q2) {
          $q2->where(function($q3) {
            $q3->where('attendance', false)->whereNull('a.student_id');
          })->orWhere(function($q3) {
            $q3->where('attendance', true)->whereNotNull('a.student_id');
          });
        })
        ->with('student');

    return $this->addExcused($query);
  }

  public function queryForLessons(array $lessons, array $students) {
    return Registration::whereIn('student_id', $students)
        ->whereIn('lesson_id', function($query) use ($lessons) {
          $query->select('l.id')
              ->from('lessons as l')
              ->whereExists(function($exists) use ($lessons) {
                $exists->select(DB::raw(1))
                    ->from('lessons as l1')
                    ->whereColumn('l1.date', 'l.date')
                    ->whereColumn('l1.number', 'l.number')
                    ->whereIn('l1.id', $lessons);
              });
        });
  }

  public function queryOrdered(Relation $registrations) {
    return $registrations
        ->join('students', 'students.id', 'registrations.student_id')
        ->orderBy('students.lastname')
        ->orderBy('students.firstname')
        ->orderBy('students.id')
        ->with('student', 'student.forms', 'student.forms.group');
  }

  public function deleteForCourse(Course $course, Date $firstDate = null, array $students = null) {
    $query = Registration::whereExists(function($exists) use ($course, $firstDate) {
      $exists->select(DB::raw(1))
          ->from('lessons as l')
          ->whereColumn('l.id', 'registrations.lesson_id')
          ->where('l.course_id', $course->id);
      if ($firstDate) {
        $exists->where('l.date', '>=', $firstDate);
      }
    });
    if ($students) {
      $query->whereIn('student_id', $students);
    }
    $query->delete();
  }

  public function deleteForLessons(array $lessons, array $students) {
    $this->queryForLessons($lessons, $students)->delete();
  }

  private function getSlotQuery(Date $start, Date $end = null) {
    return RepositoryHelper::inRange(DB::table('lessons')->distinct()->select(['date', 'number']), $start, $end);
  }

  private function addExcused($query) {
    return $query
        ->select('a.student_id as excused')
        ->leftJoin('absences as a', function($join) {
          $join->on('a.date', 'l.date')
              ->on('a.number', 'l.number')
              ->on('a.student_id', 'registrations.student_id');
        });
  }

}
