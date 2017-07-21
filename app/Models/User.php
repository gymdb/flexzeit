<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Representation of a User (teacher or student)
 *
 * @package App\Models
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property string $password
 * @property string $image
 */
abstract class User extends AuthUser {

  protected $hidden = ['password'];
  protected $rememberTokenName = null;

  public function getAuthIdentifier() {
    return ['type' => $this->typeString(), 'id' => parent::getAuthIdentifier()];
  }

  abstract public function typeString();

  public function isStudent() {
    return false;
  }

  public function isTeacher() {
    return false;
  }

  public function name() {
    return $this->lastname . ' ' . $this->firstname;
  }

}