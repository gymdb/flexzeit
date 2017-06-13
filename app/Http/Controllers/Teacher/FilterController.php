<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\MiscService;
use App\Services\TeacherService;

/**
 * Controller for miscellaneous pages for teachers
 *
 * @package App\Http\Controllers\Teacher
 */
class FilterController extends Controller {

  /** @var MiscService */
  private $miscService;

  /**
   * Create a new controller instance.
   *
   * @param MiscService $miscService
   */
  public function __construct(TeacherService $miscService) {
    $this->miscService = $miscService;
  }

  public function getStudents(Group $group) {
    $students = $this->miscService->getStudents($group);
    return response()->json($students);
  }

}
