<?php

namespace App\Repositories\Eloquent;

use App\Helpers\DateConstraints;
use App\Models\BugReport;

class BugReportRepository implements \App\Repositories\BugReportRepository {

  use RepositoryTrait;

  protected $noNumber = true;

  public function queryReports(DateConstraints $constraints, bool $showTrashed = false) {
    $query = $this->inRange(BugReport::query(), $constraints);
    if ($showTrashed) {
      $query->withTrashed();
    }
    return $query;
  }

}
