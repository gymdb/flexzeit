<?php

namespace App\Http\Requests\Course;

use App\Specifications\ObligatorySpecification;

class EditObligatoryCourseRequest extends EditCourseRequest implements ObligatorySpecification {

  use ObligatoryCourseTrait;

}