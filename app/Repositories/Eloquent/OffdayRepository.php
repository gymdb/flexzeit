<?php
namespace App\Repositories\Eloquent;

use App\Models\Offday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class OffdayRepository implements \App\Repositories\OffdayRepository {

  /**
   * @param int $id
   * @return Offday|null
   */
  public function find($id) {
    return Offday::find($id);
  }

  /**
   * @return Collection
   */
  public function all() {
    return Offday::all();
  }

  public function inRange(Carbon $start, Carbon $end = null, $dayOfWeek = null, Builder $query = null) {
    return RepositoryHelper::inRange($query ?: Offday::query()->doesntHave('group'), $start, $end, $dayOfWeek);
  }

}