<?php

namespace App\Http\Requests\Course;

use App\Specifications\ObligatorySpecification;

class CreateObligatoryCourseRequest extends CreateCourseRequest implements ObligatorySpecification {

  use ObligatoryCourseTrait;

}
