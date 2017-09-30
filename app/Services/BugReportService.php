<?php

namespace App\Services;

use App\Helpers\Date;
use App\Models\User;
use Illuminate\Support\Collection;

interface BugReportService {

  /**
   * Create a bug report
   *
   * @param User $user
   * @param string $text
   */
  public function createBugReport(User $user, $text);

  /**
   * Get bug reports for a student for returning as JSON
   *
   * @param Date|null $start
   * @param Date|null $end
   * @param bool $showTrashed
   * @return Collection <array>
   */
  public function getMappedBugReports(Date $start = null, Date $end = null, bool $showTrashed = false);

}
