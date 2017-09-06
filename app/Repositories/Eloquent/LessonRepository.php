<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Room;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
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

  public function queryForTeacher(Teacher $teacher = null, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    return $this->queryInRange($start, $end, $dayOfWeek, $number, $showCancelled, $withCourse, $teacher ? $teacher->lessons() : null);
  }

  public function queryForStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, $number = null, $showCancelled = false, $withCourse = false) {
    return $this->queryInRange($start, $end, $dayOfWeek, $number, $showCancelled, $withCourse, $student->lessons());
  }

  public function queryForOccupation(Collection $lessons, Teacher $teacher) {
    return Lesson::where('cancelled', false)
        ->where('teacher_id', '!=', $teacher->id)
        ->where(function($query) use ($lessons) {
          foreach ($lessons as $lesson) {
            $query->orWhere([
                'date'   => $lesson['date'],
                'number' => $lesson['number']
            ]);
          }
        });
  }

  public function queryAvailable(Student $student, Date $date, Teacher $teacher = null, Subject $subject = null, $type = null) {
    $query = $this->queryInRange($date, null, null, null, false, false, $teacher ? $teacher->lessons() : null)
        // Must not be part of an obligatory course
        ->whereNotExists(function($exists) {
          $exists->select(DB::raw(1))
              ->from('course_group as c')
              ->whereColumn('c.course_id', 'lessons.course_id');
        })
        // Must be the first lesson of a course
        ->whereNotExists(function($exists) {
          $exists->select(DB::raw(1))
              ->from('lessons as sub')
              ->whereColumn('sub.course_id', 'lessons.course_id')
              ->whereColumn('sub.date', '<', 'lessons.date');
        });

    // The student must not have registrations or offdays for the lesson (or all lessons for the course)
    $query->whereNotExists(function($exists) use ($student) {
      $exists->select(DB::raw(1))
          ->from('lessons as d')
          ->where('d.cancelled', false)
          ->where(function($sub) {
            $sub->whereColumn('d.id', 'lessons.id')
                ->orWhereColumn('d.course_id', 'lessons.course_id');
          })
          ->where(function($sub) use ($student) {
            $this->restrictToTimetable($sub, $student, true);
            $this->excludeExistingRegistrations($sub, $student, true);
            $this->excludeOffdays($sub, $student, true);
          });
    });

    // Only show lessons with free spots
    $query->where(function($or) {
      $sub = $this->getParticipantsQuery();
      $or->whereRaw("({$sub->toSql()}) < ({$this->getMaxStudentsQuery()})")
          ->addBinding($sub->getBindings())
          ->orWhereExists(function($exists) {
            $exists->select(DB::raw(1))
                ->from('courses')
                ->whereColumn('courses.id', 'lessons.course_id')
                ->whereNull('courses.maxstudents');
          });
    });

    // Limit to allowed years for course
    $year = $student->forms()->take(1)->pluck('year')->first();
    $query->whereNotExists(function($query) use ($year) {
      $query->select(DB::raw(1))
          ->from('courses as c')
          ->whereColumn('c.id', 'lessons.course_id')
          ->where(function($or) use ($year) {
            $or->where(function($sub) use ($year) {
              $sub->whereNotNull('c.yearfrom');
              if ($year) {
                $sub->where('c.yearfrom', '>', $year);
              }
            })->orWhere(function($sub) use ($year) {
              $sub->whereNotNull('c.yearto');
              if ($year) {
                $sub->where('c.yearto', '<', $year);
              }
            });
          });
    });

    if ($subject) {
      $query->whereIn('lessons.teacher_id', function($in) use ($subject) {
        $in->select('s.teacher_id')
            ->from('subject_teacher as s')
            ->where('s.subject_id', $subject->id);
      });
    }

    if ($type) {
      $query->whereIn('lessons.room_id', function($in) use ($type) {
        $in->select('r.id')
            ->from('rooms as r')
            ->where('r.type', $type);
      });
    }

    return $query;
  }

  public function addParticipants(Builder $query) {
    return $query->selectSub($this->getParticipantsQuery(), 'participants');
  }

  private function getMaxStudentsQuery() {
    $course = Course::select('courses.maxstudents')->whereColumn('courses.id', 'lessons.course_id');
    $room = Room::select('rooms.capacity')->whereColumn('rooms.id', 'lessons.room_id');
    return DB::raw("CASE WHEN lessons.course_id IS NOT NULL THEN ({$course->toSql()}) ELSE ({$room->toSql()}) END");
  }

  public function queryForGroups(array $groups, Date $start, Date $end = null, $dayOfWeek = null, $number = null, Course $exclude = null) {
    $query = $this->queryInRange($start, $end, $dayOfWeek, $number)
        ->whereIn('lessons.course_id', function($exists) use ($groups) {
          $exists->select('g.course_id')
              ->from('course_group as g')
              ->whereIn('g.group_id', $groups);
        });
    if ($exclude) {
      $query->where('lessons.course_id', '!=', $exclude->id);
    }

    return $query;
  }

  public function assignCourse(Collection $lessons, Course $course) {
    if ($lessons->isNotEmpty()) {
      Lesson::whereIn('id', $lessons->pluck('id'))->update(['course_id' => $course->id]);
    }
  }

  public function createWithCourse(Collection $lessons, Course $course) {
    if ($lessons->isNotEmpty()) {
      $lessons->each(function(Lesson $lesson) use ($course) {
        $lesson->course_id = $course->id;
      });

      Lesson::insert(array_map(function(Lesson $lesson) {
        return $lesson->getAttributes();
      }, $lessons->all()));
    }
  }

}
