<?php

namespace Tests\Traits;

use App\Helpers\Date;
use App\Models\Lesson;

trait MockLessonsTrait {

  use AbstractTrait;

  protected abstract function shouldReceive($key, $method);

  protected function mockLessons(array $methods = ['forTeacher' => null, 'forGroups' => null], array $lessons = [], array $associate = []) {
    foreach ($methods as $method => $expectedParam) {
      $this->shouldReceive('lessons', $method)
          ->andReturnUsing(function($params, Date $start, Date $end = null, $dayOfWeek = null, array $numbers = null,
              $showCancelled = false, $withCourse = false) use ($expectedParam, $lessons, $associate) {
            if (is_null($expectedParam)) {
              $this->assertEquals($expectedParam, $params);
            }

            return $this->mockResult(collect($lessons)
                ->map(function($lesson) {
                  return is_array($lesson) ? $lesson : ['date' => $lesson];
                })
                ->filter(function($lesson) use ($start, $end, $dayOfWeek, $showCancelled, $withCourse) {
                  $date = $lesson['date'];
                  return $start <= $date
                      && (is_null($end) ? $start >= $date : $end >= $date)
                      && (is_null($dayOfWeek) || $dayOfWeek === $date->dayOfWeek)
                      && ($showCancelled || empty($lesson['cancelled']))
                      && (!$withCourse || !empty($lesson['withCourse']));
                })
                ->flatMap(function($lesson) use ($numbers, $associate) {
                  if (!empty($lesson['number'])) {
                    return [$this->mockModel(
                        Lesson::class,
                        ['date' => $lesson['date'], 'number' => $lesson['number'], 'cancelled' => !empty($lesson['cancelled'])],
                        $associate
                    )];
                  }

                  $result = [];
                  foreach ($numbers as $number) {
                    $result[] = $this->mockModel(
                        Lesson::class,
                        ['date' => $lesson['date'], 'number' => $number, 'cancelled' => !empty($lesson['cancelled'])],
                        $associate
                    );
                  }
                  return $result;
                })
            );
          });
    }
  }

}