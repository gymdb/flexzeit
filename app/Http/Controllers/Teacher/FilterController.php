<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\MiscService;
use Illuminate\Http\JsonResponse;

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
  public function __construct(MiscService $miscService) {
    $this->miscService = $miscService;
  }

  /**
   * Get all students for a group
   *
   * @param Group $group
   * @return JsonResponse
   */
  public function getStudents(Group $group) {
    $students = $this->miscService->getStudents($group);
    return response()->json($students);
  }

}
