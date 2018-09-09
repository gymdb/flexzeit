<?php

namespace App\Http\Controllers\Teacher;

use App\Helpers\Date;
use App\Helpers\DateConstraints;
use App\Http\Controllers\Controller;
use App\Models\BugReport;
use App\Models\User;
use App\Services\BugReportService;
use App\Services\ConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BugReportController extends Controller {

  /** @var ConfigService */
  private $configService;

  /** @var BugReportService */
  private $bugReportService;

  /**
   * Create a new controller instance.
   *
   * @param ConfigService $configService
   * @param BugReportService $bugReportService
   */
  public function __construct(ConfigService $configService, BugReportService $bugReportService) {
    $this->configService = $configService;
    $this->bugReportService = $bugReportService;

    $this->middleware('transaction', ['only' => ['createBugReport']]);
  }

  /**
   * Show the list page of bug reports
   *
   * @return View
   */
  public function showBugReports() {
    $this->authorize('show', BugReport::class);

    $minDate = $this->configService->getYearStart();
    $maxDate = $this->configService->getYearEnd(Date::today());

    return view('teacher.bugreports', compact('minDate', 'maxDate'));
  }

  /**
   * Get reports within a given timeframe
   *
   * @param Date|null $start
   * @param Date|null $end
   * @param bool $showTrashed
   * @return JsonResponse
   */
  public function getBugReports(Date $start = null, Date $end = null, bool $showTrashed = false) {
    $this->authorize('show', BugReport::class);

    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: Date::today();
    $constraints = new DateConstraints($start, $end->addDay());

    $reports = $this->bugReportService->getMappedBugReports($constraints, $showTrashed);
    return response()->json($reports);
  }

  /**
   * Trash a bug report
   *
   * @param BugReport $report
   * @return JsonResponse
   */
  public function trash(BugReport $report) {
    $this->authorize('trash', $report);
    $success = $report->delete();
    return response()->json(['success' => $success]);
  }

  /**
   * Restore a trashed bug report
   *
   * @param BugReport $report
   * @return JsonResponse
   */
  public function restore(BugReport $report) {
    $this->authorize('trash', $report);
    $success = $report->restore();
    return response()->json(['success' => $success]);
  }

  /**
   * Create a bug report
   *
   * @param string $text
   * @return JsonResponse
   */
  public function createBugReport($text) {
    /** @var User $user */
    $user = Auth::user();
    $this->bugReportService->createBugReport($user, $text);
    return response()->json(['success' => true]);
  }

}
