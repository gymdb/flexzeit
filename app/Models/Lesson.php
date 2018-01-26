<?php

namespace App\Models;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a lesson
 *
 * @package App\Models
 * @property int $id
 * @property Teacher $teacher
 * @property Date $date
 * @property int $number
 * @property Course $course
 * @property bool $cancelled
 * @property bool $generated
 * @property Room $room
 * @property Collection $registrations
 * @property Collection $students
 */
class Lesson extends Model {

  public $timestamps = false;
  protected $casts = ['date' => 'date', 'cancelled' => 'boolean', 'generated' => 'boolean', 'number' => 'int'];
  protected $fillable = ['teacher_id', 'date', 'number', 'generated', 'room_id'];

  public function teacher() {
    return $this->belongsTo(Teacher::class);
  }

  public function course() {
    return $this->belongsTo(Course::class);
  }

  public function room() {
    return $this->belongsTo(Room::class);
  }

  public function registrations() {
    return $this->hasMany(Registration::class);
  }

  public function students() {
    return $this->belongsToMany(Student::class, 'registrations');
  }

  public function groups() {
    return $this->belongsToMany(Group::class, 'course_group', 'course_id', 'course_id');
  }

}
