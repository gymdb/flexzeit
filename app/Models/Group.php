<?php

namespace App\Models;

use App\Helpers\BelongsToManyKey;
use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a group
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property Form $form
 * @property Collection $courses
 * @property Collection $offdays
 * @property Collection $students
 * @property Collection $teachers
 */
class Group extends Model {

  public $timestamps = false;

  public function courses() {
    return $this->belongsToMany(Course::class);
  }

  public function offdays() {
    return $this->hasMany(Offday::class);
  }

  public function students() {
    return $this->belongsToMany(Student::class);
  }

  public function teachers() {
    return $this->belongsToMany(Teacher::class);
  }

  public function form() {
    return $this->hasOne(Form::class);
  }

  public function lessons() {
    return $this->hasManyThrough(Lesson::class, Course::class);
  }

  public function registrations() {
    return new BelongsToManyKey(
        (new Registration())->newQuery(), $this, 'group_student', 'group_id', 'student_id', $this->guessBelongsToManyRelation()
    );
  }

}
