<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class RegistrationRepository implements \App\Repositories\RegistrationRepository {

  public function inRange(Date $start, Date $end = null, $dayOfWeek = null, Relation $relation = null) {
    $query = $relation ? $relation->getQuery() : Registration::query();
    return RepositoryHelper::inRange($query->join('lessons', 'lessons.id', 'lesson_id'), $start, $end, $dayOfWeek);
  }

  public function forTeacher(Teacher $teacher, Date $start, Date $end = null, $dayOfWeek = null) {
    return $this->inRange($start, $end, $dayOfWeek)
        ->join('students', 'students.id', 'registrations.student_id')
        ->where('lessons.teacher_id', $teacher->id);
  }

  public function forStudent(Student $student, Date $start, Date $end = null, $dayOfWeek = null, Teacher $teacher = null, Subject $subject = null) {
    $query = $this->inRange($start, $end, $dayOfWeek, $student->registrations());
    if ($teacher) {
      $query->where('lessons.teacher_id', $teacher->id);
    }
    if ($subject) {
      $query->where(function($q1) use ($subject) {
        $q1->whereExists(function($q2) use ($subject) {
          $q2->select(DB::raw(1))
              ->from('courses')
              ->whereColumn('courses.id', 'lessons.course_id')
              ->where('courses.subject_id', $subject->id);
        });
        $q1->orWhereExists(function($q2) use ($subject) {
          $q2->select(DB::raw(1))
              ->from('subject_teacher')
              ->whereColumn('subject_teacher.teacher_id', 'lessons.teacher_id')
              ->where('subject_teacher.subject_id', $subject->id);
        });
      });
    }
    return $query;
  }

}