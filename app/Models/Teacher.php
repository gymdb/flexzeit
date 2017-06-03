<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a teacher
 *
 * @package App\Models
 * @property bool $admin
 * @property Form $form The form of which the teacher is the head
 * @property Collection $groups
 * @property Collection $lessons
 * @property Collection $subjects
 */
class Teacher extends User {

  public $timestamps = false;
  protected $casts = ['admin' => 'boolean'];

  public function typeString() {
    return 'teacher';
  }

  public function isTeacher() {
    return true;
  }

  public function form() {
    return $this->hasOne(Form::class, 'kv_id');
  }

  public function groups() {
    return $this->belongsToMany(Group::class);
  }

  public function lessons() {
    return $this->hasMany(Lesson::class);
  }

  public function subjects() {
    return $this->belongsToMany(Subject::class);
  }

}
