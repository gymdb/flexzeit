<?php

namespace App\Http\Requests\Course;

use App\Specifications\EditCourseSpecification;

abstract class EditCourseRequest extends CourseRequest implements EditCourseSpecification {

}
