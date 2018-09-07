<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * Representation of a registration for a lesson
 *
 * @package App\Models
 * @property int $id
 * @property Lesson $lesson
 * @property Student $student
 * @property bool $obligatory
 * @property bool $byteacher
 * @property bool $attendance
 * @property string $documentation
 * @property string $feedback
 * @property Carbon $registered_at
 */
class Registration extends Model {

  const CREATED_AT = 'registered_at';
  const UPDATED_AT = null;

  protected $casts = ['obligatory' => 'boolean', 'byteacher' => 'boolean', 'attendance' => 'boolean'];
  protected $fillable = ['lesson', 'student', 'obligatory', 'byteacher'];

  public function lesson() {
    return $this->belongsTo(Lesson::class);
  }

  public function student() {
    return $this->belongsTo(Student::class);
  }

}
