<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Representation of a configuration option stored in the database
 *
 * @package App\Models
 * @property string $key
 * @property string $value
 */
class ConfigOption extends Model {

  protected $table = 'config';
  protected $primaryKey = 'key';
  protected $keyType = 'string';
  public $incrementing = false;
  public $timestamps = false;
  protected $casts = ['value' => 'json'];
  protected $fillable = ['key'];

}
