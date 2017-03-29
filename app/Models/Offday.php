<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Representation of a day without school
 *
 * @package App\Models
 * @property int $id
 * @property Carbon $date
 * @property Group $group
 */
class Offday extends Model {

  public $timestamps = false;
  protected $casts = ['date' => 'date'];

  public function group() {
    return $this->belongsTo(Group::class);
  }

}
