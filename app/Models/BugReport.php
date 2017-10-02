<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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

  use SoftDeletes;

  const CREATED_AT = 'date';
  const UPDATED_AT = null;

  protected $fillable = ['text'];
  protected $table = 'bugreports';

  protected $dates = ['deleted_at'];

  public function teacher() {
    return $this->belongsTo(Teacher::class);
  }

  public function student() {
    return $this->belongsTo(Student::class);
  }

  /**
   * Overridden route model resolver to include trashed reports in retrieved models
   *
   * @param  mixed $value
   * @return BugReport|null
   */
  public function resolveRouteBinding($value) {
    return $this->withTrashed()->where($this->getRouteKeyName(), $value)->first();
  }

}
