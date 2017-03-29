<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as AuthUser;

abstract class User extends AuthUser {

  protected $hidden = ['password'];
  protected $rememberTokenName = null;

  public function getAuthIdentifier() {
    return ['type' => $this->typeString(), 'id' => parent::getAuthIdentifier()];
  }

  abstract public function typeString();

}