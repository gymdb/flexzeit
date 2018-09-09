<?php

namespace App\Repositories\Eloquent;

use App\Helpers\DateConstraints;
use App\Models\Offday;
use App\Models\Student;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class OffdayRepository implements \App\Repositories\OffdayRepository {

  use RepositoryTrait;

  public function queryInRange(DateConstraints $constraints, Relation $relation = null) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = $this->inRange($relation ? $relation->getQuery() : Offday::whereNull('group_id'), $constraints);
    if (!$constraints->getNumbers()) {
      $query->whereNull('number');
    }
    return $query;
  }

  public function queryForLessonsWithStudent(Collection $lessons, Student $student) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = Offday::whereIn('group_id', $student->groups()->select('id')->getQuery());
    return $this->restrictToLessons($query, $lessons);
  }

  public function queryForLessonsWithGroups(Collection $lessons, array $groups) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    $query = Offday::whereIn('group_id', $this->relatedGroups($groups));
    return $this->restrictToLessons($query, $lessons);
  }

  public function queryWithoutGroup() {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return Offday::whereNull('group_id');
  }

  public function queryWithGroup(DateConstraints $constraints) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    return $this->inRange(Offday::whereNotNull('group_id'), $constraints);
  }

  public function deleteById(Collection $ids) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    Offday::whereIn('id', $ids)->delete();
  }

  public function insert(Collection $offdays) {
    /** @noinspection PhpDynamicAsStaticMethodCallInspection */
    Offday::insert($offdays->toArray());
  }

}
