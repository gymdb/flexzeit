<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;

interface CourseRepository {

  /**
   * Return a teacher's courses
   *
   * @param Teacher $teacher
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function forTeacher(Teacher $teacher, Date $start, Date $end = null);

}
