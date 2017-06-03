<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\Relation;

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
      $query->join('lessons', 'lessons.id', 'registrations.registration_id')
          ->where('lessons.teacher_id', $teacher->id);
    }
    return $query;
  }

}