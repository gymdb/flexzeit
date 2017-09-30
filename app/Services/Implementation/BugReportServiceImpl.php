<?php

namespace App\Services\Implementation;

use App\Exceptions\BugReportException;
use App\Helpers\Date;
use App\Models\BugReport;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Repositories\BugReportRepository;
use App\Services\BugReportService;
use App\Services\ConfigService;

class BugReportServiceImpl implements BugReportService {

  /** @var ConfigService */
  private $configService;

  /** @var BugReportRepository */
  private $bugReportRepository;

  /**
   * LessonService constructor for injecting dependencies.
   *
   * @param ConfigService $configService
   * @param BugReportRepository $bugReportRepository
   */
  public function __construct(ConfigService $configService, BugReportRepository $bugReportRepository) {
    $this->configService = $configService;
    $this->bugReportRepository = $bugReportRepository;
  }

  public function createBugReport(User $user, $text) {
    if (!is_string($text)) {
      throw new BugReportException(BugReportException::INVALID_TEXT);
    }

    $bugReport = new BugReport(['text' => $text]);
    if ($user instanceof Teacher) {
      $bugReport->teacher()->associate($user);
    } else if ($user instanceof Student) {
      $bugReport->student()->associate($user);
    } else {
      throw new BugReportException(BugReportException::INVALID_USER);
    }

    $bugReport->save();
  }

  public function getMappedBugReports(Date $start = null, Date $end = null, bool $showTrashed = false) {
    $start = $start ?: $this->configService->getYearStart();
    $end = $end ?: Date::today();

    return $this->bugReportRepository
        ->queryReports($start, $end->addDay(), $showTrashed)
        ->with('teacher:id,lastname,firstname', 'student:id,lastname,firstname', 'student.forms:forms.group_id', 'student.forms.group:id,name')
        ->get()
        ->map(function(BugReport $report) {
          $author = null;
          if ($report->teacher) {
            $author = $report->teacher->name();
          } else if ($report->student) {
            $author = $report->student->name() . $report->student->formsString();
          }

          return [
              'id'      => $report->id,
              'text'    => $report->text,
              'date'    => $report->date->toIso8601String(),
              'author'  => $author,
              'trashed' => $report->trashed()
          ];
        });
  }

}
