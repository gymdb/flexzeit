<?php
namespace Tests\Traits;

use App\Models\Offday;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

trait MockOffdaysTrait {

  use AbstractTrait;

  protected function mockOffdaysInRange(array $dates = [], Builder $query = null) {
    $exp = $this->shouldReceive('offdays', 'inRange')
        ->andReturnUsing(function(Carbon $start, Carbon $end = null, $dayOfWeek = null) use ($dates) {
          return $this->mockResult(collect($dates)
              ->filter(function(Carbon $date) use ($start, $end, $dayOfWeek) {
                return $start <= $date
                    && (is_null($end) ? $start >= $date : $end >= $date)
                    && (is_null($dayOfWeek) || $dayOfWeek === $date->dayOfWeek);
              })
              ->map(function(Carbon $date) {
                return $this->mockOffday($date);
              })
          );
        });
    if (!is_null($query)) {
      $exp->with(\Mockery::any(), \Mockery::any(), \Mockery::any(), $query);
    }
  }

  protected function mockOffday(Carbon $date) {
    $offday = $this->mockModel(Offday::class, ['date' => $date]);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $offday->shouldReceive(['offsetExists' => true, 'offsetGet' => $date])->with('date');

    return $offday;
  }

}