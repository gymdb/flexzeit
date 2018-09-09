<?php

namespace App\Services;

use App\Helpers\DateConstraints;
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
   * @param DateConstraints $constraints
   * @param bool $showTrashed
   * @return Collection <array>
   */
  public function getMappedBugReports(DateConstraints $constraints, bool $showTrashed = false);

}
