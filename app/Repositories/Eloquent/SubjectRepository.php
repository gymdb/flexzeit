<?php

namespace App\Repositories\Eloquent;

use App\Models\Subject;

class SubjectRepository implements \App\Repositories\SubjectRepository {

  public function query() {
    return Subject::query();
  }

}