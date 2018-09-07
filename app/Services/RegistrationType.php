<?php

namespace App\Services;

final class RegistrationType {

  /** @var bool */
  private $obligatory;

  /** @var bool */
  private $byTeacher;

  /** @var bool */
  private $force;

  private function __construct(bool $obligatory, bool $byTeacher, bool $force) {
    $this->obligatory = $obligatory;
    $this->byTeacher = $byTeacher;
    $this->force = $force;
  }

  /**
   * @return bool true iff the registration should be flagged as obligatory (unregister not possible by student)
   */
  public function isObligatory(): bool {
    return $this->obligatory;
  }

  /**
   * @return bool true iff registration should be flagged as done by teacher (for list of manual registrations)
   */
  public function isByTeacher(): bool {
    return $this->byTeacher;
  }

  /**
   * @return bool true iff validation of course/lesson limitations should be skipped
   */
  public function ignoreLimitations(): bool {
    return $this->byTeacher || $this->obligatory;
  }

  /**
   * @return bool true iff validation should be skipped completely
   */
  public function ignoreValidation(): bool {
    return $this->force;
  }

  /**
   * @return bool true iff existing registrations should get unregistered
   */
  public function unregisterExisting(): bool {
    return $this->force;
  }

  /**
   * @return RegistrationType An instance with no flags set
   */
  public static function BY_STUDENT() {
    return new self(false, false, false);
  }

  /**
   * @return RegistrationType An instance with obligatory, ignoreValidation and unregisterExisting flag set
   */
  public static function OBLIGATORY() {
    return new self(true, false, true);
  }

  /**
   * @param bool $admin Iff true all flags will be set
   * @return RegistrationType An instance with obligatory, byTeacher and ignoreLimitations flag or all flags set
   */
  public static function BY_TEACHER(bool $admin = false) {
    return new self(true, true, $admin);
  }

  /**
   * @return RegistrationType An instance with obligatory flag set
   */
  public static function SUBSTITUTED() {
    return new self(true, false, false);
  }
}
