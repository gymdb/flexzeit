<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a student
 *
 * @package App\Models
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property string $password
 * @property string $image
 * @property Collection $absences
 * @property Collection $groups
 * @property Collection $registrations
 * @property Collection $lessons
 */
class Student extends User {

  public $timestamps = false;

  public function typeString() {
    return 'student';
  }

  public function absences() {
    return $this->hasMany(Absence::class);
  }

  public function groups() {
    return $this->belongsToMany(Group::class);
  }

  public function registrations() {
    return $this->hasMany(Registration::class);
  }

  public function lessons() {
    return $this->hasManyThrough(Lesson::class, Registration::class);
  }

}
