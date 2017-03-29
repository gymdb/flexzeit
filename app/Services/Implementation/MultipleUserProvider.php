<?php

namespace App\Services\Implementation;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;

/**
 * UserProvider using both teachers and students as source
 *
 * @package App\Services
 */
class MultipleUserProvider implements UserProvider {

  /**
   * @var UserProvider
   */
  protected $teacherProvider;

  /**
   * @var UserProvider
   */
  protected $studentProvider;

  function __construct() {
    $this->teacherProvider = Auth::createUserProvider('teacher');
    $this->studentProvider = Auth::createUserProvider('student');
  }

  protected function execute($type, Closure $f) {
    switch ($type) {
      case 'teacher':
        return $f($this->teacherProvider);
      case 'student':
        return $f($this->studentProvider);
    }

    return null;
  }

  /**
   * Retrieve a user by their unique identifier.
   *
   * @param  mixed $identifier
   * @return Authenticatable|null
   */
  public function retrieveById($identifier) {
    if (!is_array($identifier) || !isset($identifier['type'])) {
      return null;
    }

    return $this->execute($identifier['type'], function(UserProvider $provider) use ($identifier) {
      return $provider->retrieveById($identifier['id']);
    });
  }

  /**
   * Retrieve a user by their unique identifier and "remember me" token.
   *
   * @param  mixed $identifier
   * @param  string $token
   * @return Authenticatable|null
   */
  public function retrieveByToken($identifier, $token) {
    // RememberToken currently not used at all
    //if (!is_array($identifier) || !isset($identifier['type'])) {
    //  return null;
    //}
    //
    //return $this->execute($identifier['type'], function(UserProvider $provider) use ($identifier, $token) {
    //  return $provider->retrieveByToken($identifier['id'], $token);
    //});

    return null;
  }

  /**
   * Update the "remember me" token for the given user in storage.
   *
   * @param Authenticatable $user
   * @param  string $token
   * @return void
   */
  public function updateRememberToken(Authenticatable $user, $token) {
    // RememberToken currently not used at all
    //if ($user instanceof User) {
    //  $this->execute($user->typeString(), function(UserProvider $provider) use ($user, $token) {
    //    return $provider->updateRememberToken($user, $token);
    //  });
    //}
  }

  /**
   * Retrieve a user by the given credentials.
   *
   * @param  array $credentials
   * @return Authenticatable|null
   */
  public function retrieveByCredentials(array $credentials) {
    if (empty($credentials)) {
      return null;
    }

    $user = $this->teacherProvider->retrieveByCredentials($credentials);
    if (!is_null($user)) {
      return $user;
    }

    return $this->studentProvider->retrieveByCredentials($credentials);
  }

  /**
   * Validate a user against the given credentials.
   *
   * @param  Authenticatable $user
   * @param  array $credentials
   * @return bool
   */
  public function validateCredentials(Authenticatable $user, array $credentials) {
    if (!($user instanceof User)) {
      return false;
    }

    return (bool)$this->execute($user->typeString(), function($provider) use ($user, $credentials) {
      return $provider->validateCredentials($user, $credentials);
    });
  }

}
