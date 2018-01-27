<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;

/**
 * Representation of a room
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $shortname
 * @property int $capacity
 * @property Collection $lessons
 */
class Room extends Model {

  public $timestamps = false;
  protected $casts = ['capacity' => 'int'];

  public function lessons() {
    return $this->hasMany(Lesson::class);
  }

}
