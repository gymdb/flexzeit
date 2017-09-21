<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a student
 *
 * @package App\Models
 * @property string $untis_id
 * @property Collection $absences
 * @property Collection $groups
 * @property Collection $forms
 * @property Collection $registrations
 * @property Collection $lessons
 */
class Student extends User {

  public $timestamps = false;

  public function typeString() {
    return 'student';
  }

  public function isStudent() {
    return true;
  }

  public function absences() {
    return $this->hasMany(Absence::class);
  }

  public function groups() {
    return $this->belongsToMany(Group::class);
  }

  public function forms() {
    return $this->belongsToMany(Form::class, 'group_student', null, 'group_id');
  }

  public function formsString() {
    return $this->forms->isEmpty() ? '' : " ({$this->forms->implode('group.name', ', ')})";
  }

  public function registrations() {
    return $this->hasMany(Registration::class);
  }

  public function lessons() {
    return $this->belongsToMany(Lesson::class, "registrations");
  }

  public function offdays() {
    return $this->belongsToMany(Offday::class, 'group_student', null, 'group_id', null, 'group_id');
  }

}
