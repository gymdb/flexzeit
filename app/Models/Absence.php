<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Absence of a student on a certain day
 *
 * @package App\Models
 * @property Student $student
 * @property Carbon $date
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
