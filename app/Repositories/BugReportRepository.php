<?php

namespace App\Repositories;

use App\Helpers\Date;
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
   * @param Date $start
   * @param Date|null $end
   * @return Builder
   */
  public function queryReports(Date $start, Date $end = null);

}
