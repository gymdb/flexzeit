<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\LessonService;
use App\Services\StudentService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for miscellaneous pages for teachers
 *
 * @package App\Http\Controllers\Teacher
 */
class TeacherController extends Controller {

  /** @var TeacherService */
  private $teacherService;

  /**
   * Create a new controller instance.
   *
   * @param TeacherService $teacherService
   */
  public function __construct(TeacherService $teacherService) {
    $this->teacherService = $teacherService;
  }

  public function getStudents(Group $group) {
    $students = $this->teacherService->getStudents($group);
    return response()->json($students);
  }

}
