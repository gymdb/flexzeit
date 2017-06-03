<?php

namespace App\Models;

use App\Helpers\Date;

/**
 * Absence of a student on a certain day
 *
 * @package App\Models
 * @property Student $student
 * @property Date $date
 */
class Absence extends Model {

  protected $casts = ['date' => 'date'];

  protected $primaryKey = 'student_id';
  public $incrementing = false;
  public $timestamps = false;

  public function student() {
    $this->belongsTo(Student::class);
  }

}
