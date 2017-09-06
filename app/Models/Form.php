<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Additional data for a form
 *
 * @package App\Models
 * @property Group $group
 * @property int $year
 * @property Teacher $kv
 * @property Collection $students
 */
class Form extends Model {

  protected $primaryKey = 'group_id';
  public $incrementing = false;
  public $timestamps = false;

  public function group() {
    return $this->belongsTo(Group::class);
  }

  public function kv() {
    return $this->belongsTo(Teacher::class, 'kv_id');
  }

  public function students() {
    return $this->belongsToMany(Student::class, 'group_student', 'group_id');
  }

}
