<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for miscellaneous pages for teachers
 *
 * @package App\Http\Controllers\Teacher
 */
class FilterController extends Controller {

  /** @var StudentService */
  private $studentService;

  /**
   * Create a new controller instance.
   *
   * @param StudentService $studentService
   */
  public function __construct(StudentService $studentService) {
    $this->studentService = $studentService;
  }

  /**
   * Get all students for a group
   *
   * @param Group $group
   * @return JsonResponse
   */
  public function getStudents(Group $group) {
    $students = $this->studentService->getStudents($group);
    return response()->json($students);
  }

}
