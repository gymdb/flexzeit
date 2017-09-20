<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;

class TeacherRepository implements \App\Repositories\TeacherRepository {

  public function query() {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return Teacher::where('hidden', false);
  }

}
