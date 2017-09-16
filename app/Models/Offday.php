<?php

namespace App\Models;

use App\Helpers\Date;

/**
 * Representation of a day without school
 *
 * @package App\Models
 * @property int $id
 * @property Date $date
 * @property Group $group
 */
class Offday extends Model {

  public $timestamps = false;
  protected $casts = ['date' => 'date'];
  protected $fillable = ['group_id', 'date', 'number'];

  public function group() {
    return $this->belongsTo(Group::class);
  }

}
