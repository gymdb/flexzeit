<?php

namespace App\Models;

use App\Helpers\Date;

/**
 * Absence of a student on a certain day
 *
 * @package App\Models
 * @property Student $student
 * @property Date $date
 * @property int $number
 */
class Absence extends Model {

  protected $primaryKey = 'student_id';
  public $incrementing = false;
  public $timestamps = false;
  protected $casts = ['date' => 'date'];
  protected $fillable = ['student_id', 'date', 'number'];

  public function student() {
    $this->belongsTo(Student::class);
  }

}
