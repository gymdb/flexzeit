<?php

namespace App\Models;

use App\Helpers\BelongsToManyKey;
use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a student
 *
 * @package App\Models
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
    return new BelongsToManyKey(
        (new Form())->newQuery(), $this, 'group_student', 'student_id', 'group_id', $this->guessBelongsToManyRelation()
    );
  }

  public function registrations() {
    return $this->hasMany(Registration::class);
  }

  public function lessons() {
    return $this->belongsToMany(Lesson::class, "registrations");
  }

  public function offdays() {
    return new BelongsToManyKey(
        (new Offday())->newQuery(), $this, 'group_student', 'student_id', 'group_id', $this->guessBelongsToManyRelation()
    );
  }

}
