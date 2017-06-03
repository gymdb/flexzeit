<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Extension of Laravel BelongsToMany implementation allowing custom name for the key in related table
 *
 * @package App\Helpers
 */
class BelongsToManyKey extends BelongsToMany {

  /**
   * Set the join clause for the relation query.
   *
   * @param  Builder|null $query
   * @return $this
   */
  protected function performJoin($query = null) {
    $query = $query ?: $this->query;

    // We need to join to the intermediate table on the related model's primary
    // key column with the intermediate table's foreign key for the related
    // model instance. Then we can set the "where" for the parent models.
    $baseTable = $this->related->getTable();

    $key = $baseTable . '.' . $this->relatedKey;
    $query->join($this->table, $key, '=', $this->getQualifiedRelatedKeyName());

    return $this;
  }

  /**
   * Touch all of the related models for the relationship.
   *
   * E.g.: Touch all roles associated with this user.
   *
   * @return void
   */
  public function touch() {
    $key = $this->relatedKey;

    $columns = [
        $this->related->getUpdatedAtColumn() => $this->related->freshTimestampString(),
    ];

    // If we actually have IDs for the relation, we will run the query to update all
    // the related model's timestamps, to make sure these all reflect the changes
    // to the parent models. This will help us keep any caching synced up here.
    if (count($ids = $this->allRelatedIds()) > 0) {
      $this->getRelated()->newQuery()->whereIn($key, $ids)->update($columns);
    }
  }

  /**
   * Get all of the IDs for the related models.
   *
   * @return \Illuminate\Support\Collection
   */
  public function allRelatedIds() {
    return $this->getQuery()->select(
        $this->getRelated()->getTable() . '.' . $this->relatedKey
    )->pluck($this->relatedKey);
  }

}