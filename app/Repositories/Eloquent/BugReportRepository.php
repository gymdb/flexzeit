<?php

namespace App\Repositories\Eloquent;

use App\Helpers\Date;
use App\Models\BugReport;

class BugReportRepository implements \App\Repositories\BugReportRepository {

  use RepositoryTrait;

  protected $noNumber = true;

  public function queryReports(Date $start, Date $end = null) {
    $query = $this->inRange(BugReport::query(), $start, $end);
    return $query->with('teacher', 'student');
  }

}