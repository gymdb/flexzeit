<?php
namespace Tests\Traits;

use App\Helpers\Date;
use App\Models\Offday;
use App\Repositories\OffdayRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;

trait MockOffdaysTrait {

  use AbstractTrait;

  protected function mockOffdaysInRange(array $dates = [], Relation $relation = null) {
    $this->mock(['offdays' => OffdayRepository::class]);
    $exp = $this->shouldReceive('offdays', 'inRange')
        ->andReturnUsing(function(Date $start, Date $end = null, $dayOfWeek = null) use ($dates) {
          return $this->mockResult(collect($dates)
              ->filter(function(Date $date) use ($start, $end, $dayOfWeek) {
                return $start <= $date
                    && (is_null($end) ? $start >= $date : $end >= $date)
                    && (is_null($dayOfWeek) || $dayOfWeek === $date->dayOfWeek);
              })
              ->map(function(Date $date) {
                return $this->mockOffday($date);
              })
          );
        });
    if (!is_null($relation)) {
      $exp->with(Mockery::any(), Mockery::any(), Mockery::any(), $relation);
    }
  }

  protected function mockOffday(Date $date) {
    $offday = $this->mockModel(Offday::class, ['date' => $date]);

    /** @noinspection PhpMethodParametersCountMismatchInspection */
    $offday->shouldReceive(['offsetExists' => true, 'offsetGet' => $date])->with('date');

    return $offday;
  }

}