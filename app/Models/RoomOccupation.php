<?php

namespace App\Models;

use App\Helpers\Date;

/**
 * Representation of an otherwise occupied room at a given time
 *
 * @package App\Models
 * @property int $id
 * @property Date $date
 * @property int $number
 * @property Room $room
 * @property Teacher $teacher
 */
class RoomOccupation extends Model {

  public $timestamps = false;
  protected $casts = ['date' => 'date'];
  protected $fillable = ['room_id', 'date', 'number', 'teacher_id'];

  public function room() {
    return $this->belongsTo(Room::class);
  }

  public function teacher() {
    return $this->belongsTo(Teacher::class);
  }

}
