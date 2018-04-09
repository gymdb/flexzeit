<?php

namespace App\Repositories;

use App\Helpers\Date;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Repository for accessing the students table
 *
 * @package App\Repository
 */
interface RoomRepository {

  /**
   * @return Builder
   */
  public function query();

  /**
   * @return Builder
   */
  public function queryTypes();

  /**
   * Build a query for all room occupations within the given range
   *
   * @param Date $start Start date
   * @param Date $end Optional end date
   * @return Builder
   */
  public function queryOccupations(Date $start, Date $end);

  /**
   * Build a query for all room occupations at the times of the given lessons
   *
   * @param Collection $lessons
   * @param Teacher $teacher
   * @return Builder
   */
  public function queryOccupationForLessons(Collection $lessons, Teacher $teacher);

  /**
   * @param Collection $ids
   */
  public function deleteOccupationsById(Collection $ids);

  /**
   * @param Collection $occupations
   */
  public function insertOccupations(Collection $occupations);

}
