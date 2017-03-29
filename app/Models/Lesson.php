<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Representation of a lesson
 *
 * @package App\Models
 * @property int $id
 * @property Teacher $teacher
 * @property Carbon $date
 * @property int $number
 * @property Course $course
 * @property string $room
 * @property bool $cancelled
 * @property Collection $registrations
 * @property Collection $students
 */
class Lesson extends Model {

  public $timestamps = false;
  protected $casts = ['date' => 'date', 'cancelled' => 'boolean'];
  protected $fillable = ['teacher', 'date', 'number'];

  public function teacher() {
    return $this->belongsTo(Teacher::class);
  }

  public function course() {
    return $this->belongsTo(Course::class);
  }

  public function registrations() {
    return $this->hasMany(Registration::class);
  }

  public function students() {
    return $this->hasManyThrough(Student::class, Registration::class);
  }

}
