<?php

namespace App\Repositories;

use App\Helpers\DateConstraints;
use Illuminate\Database\Eloquent\Builder;

/**
 * Repository for accessing the bug reports table
 *
 * @package App\Repository
 */
interface BugReportRepository {

  /**
   * Query reports within a given timeframe
   *
   * @param DateConstraints $constraints
   * @param bool $showTrashed
   * @return Builder
   */
  public function queryReports(DateConstraints $constraints, bool $showTrashed = false);

}
