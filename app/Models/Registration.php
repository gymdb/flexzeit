<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representation of a registration for a lesson
 *
 * @package App\Models
 * @property int $id
 * @property Lesson $lesson
 * @property Student $student
 * @property bool $obligatory
 * @property bool $present
 * @property string $documentation
 */
class Registration extends Model {

  public $timestamps = false;
  protected $casts = ['obligatory' => 'boolean', 'present' => 'boolean'];

  public function lesson() {
    return $this->belongsTo(Lesson::class);
  }

  public function student() {
    return $this->belongsTo(Student::class);
  }

}
