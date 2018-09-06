<?php

abstract class UserSession {
  public $username;
}

class TeacherSession extends UserSession {
}

class HeadmasterSession extends TeacherSession {
}

class AdminSession extends TeacherSession {
}

class KvSession extends TeacherSession {
}

class StudentSession extends UserSession {
}
