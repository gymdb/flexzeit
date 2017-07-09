<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a course
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Subject $subject
 * @property int $maxstudents
 * @property string $room
 * @property int $yearfrom
 * @property int $yearto
 * @property Collection $groups
 * @property Collection $lessons
 */
class Course extends Model {

  public $timestamps = false;

  public function groups() {
    return $this->belongsToMany(Group::class)->orderBy('name');
  }

  public function lessons() {
    return $this->hasMany(Lesson::class);
  }

  public function firstLesson() {
    return $this->lessons()->orderBy('date')->orderBy('number')->first();
  }

  public function lastLesson() {
    return $this->lessons()->orderBy('date', 'desc')->orderBy('number', 'desc')->first();
  }

  public function subject() {
    return $this->belongsTo(Subject::class);
  }

  public function registrations() {
    return $this->hasManyThrough(Registration::class, Lesson::class);
  }

  public function teacher() {
    return $this->belongsToMany(Teacher::class, 'lessons');
  }

}
