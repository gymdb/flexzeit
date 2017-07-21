<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * Representation of a registration for a lesson
 *
 * @package App\Models
 * @property int $id
 * @property Teacher|null $teacher
 * @property Student|null $student
 * @property string $text
 * @property Carbon $date
 */
class BugReport extends Model {

  const CREATED_AT = 'date';
  const UPDATED_AT = 'date';

  protected $fillable = ['text'];
  protected $table = 'bugreports';

  public function teacher() {
    return $this->belongsTo(Teacher::class);
  }

  public function student() {
    return $this->belongsTo(Student::class);
  }

}
