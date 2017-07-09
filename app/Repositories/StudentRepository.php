<?php

namespace App\Repositories;

use App\Helpers\Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the teachers table
 *
 * @package App\Repository
 */
interface StudentRepository {

  /**
   * @param Collection|int[] $groups
   * @return Builder
   */
  public function queryForGroups($groups);

  /**
   * @param Collection $ids
   * @return Builder
   */
  public function queryForUntisId(Collection $ids);

  /**
   * @param Date $date
   */
  public function deleteAbsences(Date $date);

  /**
   * @param Collection $absences
   */
  public function insertAbsences(Collection $absences);

}
