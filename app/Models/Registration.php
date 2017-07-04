<?php

namespace App\Models;

/**
 * Representation of a registration for a lesson
 *
 * @package App\Models
 * @property int $id
 * @property Lesson $lesson
 * @property Student $student
 * @property bool $obligatory
 * @property bool $attendance
 * @property string $documentation
 * @property string $feedback
 */
class Registration extends Model {

  public $timestamps = false;
  protected $casts = ['obligatory' => 'boolean', 'attendance' => 'boolean'];
  protected $fillable = ['lesson', 'student', 'obligatory'];

  public function lesson() {
    return $this->belongsTo(Lesson::class);
  }

  public function student() {
    return $this->belongsTo(Student::class);
  }

}
