<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Representation of a subject
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property Collection $teachers
 * @property Collection $courses
 */
class Subject extends Model {

  public $timestamps = false;

  public function teachers() {
    return $this->belongsToMany(Teacher::class);
  }

  public function courses() {
    return $this->hasMany(Course::class);
  }

}
