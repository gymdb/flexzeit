<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;

class TeacherRepository implements \App\Repositories\TeacherRepository {

  public function query() {
    return Teacher::query();
  }

}