<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
    return $this->belongsToMany(Group::class);
  }

  public function lessons() {
    return $this->hasMany(Lesson::class);
  }

  public function subject() {
    return $this->belongsTo(Subject::class);
  }

}
