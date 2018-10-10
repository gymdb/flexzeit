<?php

namespace App\Services;

use App\Helpers\Date;
use Illuminate\Support\Collection;

/**
 * Service for accessing WebUntis
 *
 * @package App\Services
 */
interface WebUntisService {

  /**
   * Get a list of all absences
   *
   * @param Date $date Date to load
   * @return Collection<array>
   */
  public function getAbsences(Date $date);

  /**
   * Get school-wide days without lessons
   *
   * @return Collection<Date> List of dates
   */
  public function getOffdays();

  /**
   * Get timetable for the specified form
   *
   * @param string $name
   * @param Date $start
   * @param Date $end
   * @return Collection<array> List of lessons
   */
  public function getGroupTimetable($name, Date $start, Date $end);

  /**
   * Get list of all substitutions
   *
   * @param Date $start
   * @param Date $end
   * @return Collection<array>
   */
  public function getSubstitutions(Date $start, Date $end);

  /**
   * Get occupations for the specified room
   *
   * @param $name
   * @param Date $start
   * @param Date $end
   * @return Collection<array> List of occupied lessons for the room
   */
  public function getRoomOccupations($name, Date $start, Date $end);

}
