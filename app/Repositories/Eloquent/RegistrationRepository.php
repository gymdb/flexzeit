<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Models\Course;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegistrationRepository implements \App\Repositories\RegistrationRepository {

  use RepositoryTrait;

  public function queryForStudent($student = null, DateConstraints $constraints, $showCancelled = false, Teacher $teacher = null, Subject $subject = null) {
    $baseQuery = $student ? $student->registrations()->getQuery() : Registration::query();
    $query = $this->inRange($baseQuery, $constraints, 'l.')
        ->join('lessons as l', 'l.id', 'lesson_id');

    if (!$showCancelled) {
      $query->where('l.cancelled', false);
    }
    if ($teacher) {
      $query->where('l.teacher_id', $teacher->id);
    }
    if ($subject) {
      $query->where(function($q1) use ($subject) {
        $q1->whereIn('l.course_id', function($q2) use ($subject) {
          $q2->select('c.id')
              ->from('courses as c')
              ->where('c.subject_id', $subject->id);
        });
        $q1->orWhereIn('l.teacher_id', function($q2) use ($subject) {
          $q2->select('s.teacher_id')
              ->from('subject_teacher as s')
              ->where('s.subject_id', $subject->id);
        });
      });
    }
      return $query;
  }

  public function queryForAllStudents(DateConstraints $constraints, $showCancelled = false, Teacher $teacher = null, Subject $subject = null) {
    $baseQuery =  Registration::query();
    $query = $this->inRange($baseQuery, $constraints, 'l.')
        ->join('lessons as l', 'l.id', 'lesson_id');

    if (!$showCancelled) {
      $query->where('l.cancelled', false);
    }
    if ($teacher) {
      $query->where('l.teacher_id', $teacher->id);
    }
    if ($subject) {
      $query->where(function($q1) use ($subject) {
        $q1->whereIn('l.course_id', function($q2) use ($subject) {
          $q2->select('c.id')
           ->from('courses as c')
           ->where('c.subject_id', $subject->id);
        });
        $q1->orWhereIn('l.teacher_id', function($q2) use ($subject) {
        $q2->select('s.teacher_id')
          ->from('subject_teacher as s')
          ->where('s.subject_id', $subject->id);
        });
      });
    }
    return $query;
  }

  public function queryDocumentation($student, DateConstraints $constraints, Teacher $teacher = null, Subject $subject = null) {
    return $this->queryForStudent($student, $constraints, false, $teacher, $subject)
        ->where(function($q1) {
          $q1->where('attendance', true)->orWhereNull('attendance');
        });
  }

  public function queryMissing(Group $group = null, Student $student = null, DateConstraints $constraints) {
    $slotQuery = $this->getSlotQuery($constraints);

    if ($student) {
      $baseQuery = Student::whereKey($student->id);
    } else if ($group) {
      $baseQuery = $group->students();
    } else {
      $baseQuery = Student::query();
    }

    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = $baseQuery
        ->crossJoin(DB::raw("({$slotQuery->toSql()}) as d"))
        ->addBinding($slotQuery->getBindings(), 'join')
        // Don't show slots where the student is known to be absent
        ->whereNotExists(function($exists) {
          $exists->select(DB::raw(1))
              ->from('absences as a')
              ->whereColumn('a.student_id', 'students.id')
              ->whereColumn('a.date', 'd.date')
              ->whereColumn('a.number', 'd.number');
        })
        ->orderBy('date')
        ->orderBy('lastname')
        ->orderBy('firstname')
        ->orderBy('number');

    $this->restrictToTimetable($query);
    $this->excludeExistingRegistrations($query);
    $this->excludeOffdays($query);

    return $query;
  }

  public function queryMissingForLesson(Lesson $lesson, Collection $groups) {
    return Student::query()
        ->whereHas('groups', function($query) use ($groups) {
          $query->whereIn('id', $groups->pluck('id'));
        })
        ->whereNotExists(function($exists) use ($lesson) {
          $exists->select(DB::raw(1))
              ->from('registrations as r')
              ->join('lessons as l', 'l.id', 'r.lesson_id')
              ->whereColumn('r.student_id', 'students.id')
              ->where('l.date', $lesson->date)
              ->where('l.number', $lesson->number)
              ->where('l.cancelled', false);
        });
  }

  public function queryMissingSportsRegistration(Group $group=null, DateConstraints $constraints) {
    if ($group) {
      $baseQuery = $group->students();
    } else {
      $baseQuery = Student::query();
    }
    $myQuery=$baseQuery->join('group_student as g','g.student_id','students.id')
    ->WHERE('g.group_id','<',11)
    ->whereNotIn('g.student_id', function($in) use ($constraints) {
        $in->select('r.student_id')
           ->from('registrations as r')
           ->join('lessons as l', 'l.id','r.lesson_id')
           ->join('courses as c', function($join) {
               $join->on('c.id', 'l.course_id');
            })
           ->where('c.category', 3)
         ->whereBetween('l.date', [$constraints->getFirstDate(), $constraints->getLastDate()]);
    })->whereNotIn('g.student_id', function($in) use ($constraints) {
        $in->select('r.student_id')
          ->from('registrations as r')
          ->join('lessons as l', 'l.id','r.lesson_id')
          ->join('rooms','rooms.id','l.room_id')
          ->where(function($query){
            $query->where ('rooms.name','like','SPm%')
              ->orWhere('rooms.name','like','SPw%');
          })
          ->whereBetween('l.date', [$constraints->getFirstDate(), $constraints->getLastDate()]);})
        ->orderBy('g.group_id')->orderBy('lastname');
    return $myQuery;
  }

  public function queryByTeacher($student = null, DateConstraints $constraints) {
    return $this->queryForStudent($student, $constraints)->where('byteacher', true);
  }

  public function querySlots(Student $student, DateConstraints $constraints) {
    $slotQuery = $this->getSlotQuery($constraints);
    $joinQuery = DB::table('lessons as l')
        ->join('registrations as r', function($join) use ($student) {
          $join->on('r.lesson_id', 'l.id')->where('r.student_id', $student->id)->where('l.cancelled', false);
        });
    $joinSql = preg_replace('/^select +\* +from +/i', '', $joinQuery->toSql());

    $query = Lesson::query()
        ->from(DB::raw("({$slotQuery->toSql()}) as d"))
        ->addBinding($slotQuery->getBindings(), 'join')
        ->leftJoin(DB::raw("({$joinSql})"), function($join) use ($student) {
          $join->on('d.date', 'l.date')->on('d.number', 'l.number');
        })
        ->addBinding($joinQuery->getBindings(), 'join')
        ->orderBy('d.date')
        ->orderBy('d.number');

    $query->where(function($sub) use ($student) {
      $sub->whereNotNull('l.id')
          ->orWhere(function($or) use ($student) {
            $this->restrictToTimetable($or, $student);
            $this->excludeSchoolWideOffdays($or);
          });
    });

    return $query;
  }

  public function queryWithExcused($student, DateConstraints $constraints, $showCancelled = false, Teacher $teacher = null,
      Subject $subject = null) {
    $query = $this->queryForStudent($student, $constraints, $showCancelled, $teacher, $subject);
    return $this->addExcused($query);
  }

  public function queryAbsent($student, DateConstraints $constraints) {
    $query = $this->queryForStudent($student, $constraints)
        ->where(function($q2) {
          $q2->where(function($q3) {
            $q3->where('attendance', false)->whereNull('a.student_id');
          })->orWhere(function($q3) {
            $q3->where('attendance', true)->whereNotNull('a.student_id');
          });
        });

    return $this->addExcused($query);
  }

  public function queryForLessons(array $lessons, array $students) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return Registration::whereIn('student_id', $students)
        ->whereIn('lesson_id', function($query) use ($lessons) {
          $query->select('l.id')
              ->from('lessons as l')
              ->where('l.cancelled', false)
              ->whereExists(function($exists) use ($lessons) {
                $exists->select(DB::raw(1))
                    ->from('lessons as l1')
                    ->whereColumn('l1.id', '!=', 'l.id')
                    ->whereColumn('l1.date', 'l.date')
                    ->whereColumn('l1.number', 'l.number')
                    ->whereIn('l1.id', $lessons);
              });
        });
  }

  public function queryExisting(array $lessons, array $students) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return Registration::whereIn('lesson_id', $lessons)->whereIn('student_id', $students);
  }

  public function queryOrdered(Relation $registrations) {
    return $registrations
        ->join('students', 'students.id', 'registrations.student_id')
        ->orderBy('students.lastname')
        ->orderBy('students.firstname')
        ->orderBy('students.id');
  }

  public function deleteForCourse(Course $course, Date $firstDate = null, array $students = null) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = Registration::whereIn('lesson_id', function($in) use ($course, $firstDate) {
      $in->select('l.id')
          ->from('lessons as l')
          ->where('l.course_id', $course->id);
      if ($firstDate) {
        $in->where('l.date', '>=', $firstDate);
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

  public function deleteDuplicate(Lesson $lesson) {
    $delete = $this->queryNoneDuplicateRegistrations($lesson, true)->pluck('registrations.id');
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    Registration::whereIn('id', $delete)->delete();
  }

  public function queryNoneDuplicateRegistrations(Lesson $lesson, $invert = false) {
    return $lesson->registrations()
        ->join('lessons as l', 'l.id', 'registrations.lesson_id')
        ->whereExists(function($exists) {
          $exists->select(DB::raw(1))
              ->from('lessons as l1')
              ->join('registrations as r', 'r.lesson_id', 'l1.id')
              ->whereColumn('l1.id', '!=', 'l.id')
              ->whereColumn('l1.date', 'l.date')
              ->whereColumn('l1.number', 'l.number')
              ->where('l1.cancelled', false)
              ->whereColumn('r.student_id', 'registrations.student_id');
        }, 'and', !$invert);
  }

  private function getSlotQuery(DateConstraints $constraints) {
    return $this->inRange(DB::table('lessons')->distinct()->select(['date', 'number']), $constraints);
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
