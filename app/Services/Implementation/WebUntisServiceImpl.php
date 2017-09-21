<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Helpers\DateRange;
use App\Services\WebUntisService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use JsonRPC\Client;

class WebUntisServiceImpl implements WebUntisService {

  /** @var Client */
  private $connection;

  /** @var boolean */
  private $authenticated = false;

  public function __construct() {
    $this->connection = new Client(config('services.untis.domain'));
  }

  protected function authenticatedConnection() {
    if (!$this->authenticated) {
      $this->authenticate();
    }
    return $this->connection;
  }

  private function authenticate() {
    $this->logout();

    $result = $this->connection->authenticate(['user' => config('services.untis.username'), 'password' => config('services.untis.password'), 'client' => 'flexzeit']);
    $this->connection->getHttpClient()->withCookies(['JSESSIONID' => $result['sessionId']]);
    $this->authenticated = true;
  }

  protected function logout() {
    if ($this->authenticated) {
      $this->connection->logout();
      $this->authenticated = false;
    }
  }

  public function getAbsences(Date $date) {
    $dateString = $date->format('Ymd');
    $result = $this->authenticatedConnection()->getAbsences(5, $dateString, $dateString);
    $this->logout();

    return collect($result)->map(function($item) {
      return [
          'id'    => $item['studentid'],
          'start' => $this->getDateTime($item['startDate'], $item['startTime']),
          'end'   => $this->getDateTime($item['endDate'], $item['endTime'])
      ];
    });
  }

  public function getOffdays() {
    $result = $this->authenticatedConnection()->getHolidays();
    $this->logout();

    return collect($result)->flatMap(function($item) {
      return DateRange::getCollection($this->getDate($item['startDate']), $this->getDate($item['endDate']));
    });
  }

  public function getGroupTimetable($name, Date $start, Date $end) {
    $result = $this->authenticatedConnection()->getTimetable([
        'options' => [
            'element'          => ['id' => $name, 'type' => 1, 'keyType' => 'name'],
            'startDate'        => $start->format('Ymd'),
            'endDate'          => $end->format('Ymd'),
            'klasseFields'     => [],
            'roomFields'       => [],
            'subjectFields'    => ['name'],
            'teacherFields'    => [],
            'showStudentgroup' => true,
        ]
    ]);
    $this->logout();

    return collect($result)->map(function($item) {
      return [
          'start'     => $this->getDateTime($item['date'], $item['startTime']),
          'end'       => $this->getDateTime($item['date'], $item['endTime']),
          'cancelled' => !empty($item['cancelled']) && $item['code'] === 'cancelled',
          'group'     => empty($item['sg']) ? null : $item['sg'],
          'flex'      => !is_null(Arr::first($item['su'], function($subject) {
            return $subject['name'] === 'Flex';
          }))
      ];
    });
  }

  public function getSubstitutions(Date $start, Date $end) {
    $result = $this->authenticatedConnection()->getSubstitutions([
        'startDate'    => $start->format('Ymd'),
        'endDate'      => $end->format('Ymd'),
        'departmentId' => 0
    ]);
    $this->logout();

    $result = collect($result)->filter(function($item) {
      return !is_null(Arr::first($item['su'], function($subject) {
        return $subject['name'] === 'Flex';
      }));
    })->flatMap(function($item) {
      return collect($item['te'])->map(function($teacher) use ($item) {
        return [
            'start'           => $this->getDateTime($item['date'], $item['startTime']),
            'end'             => $this->getDateTime($item['date'], $item['endTime']),
            'type'            => $item['type'],
            'room'            => collect($item['ro'])->pluck('name'),
            'originalTeacher' => $teacher['orgname'] ?? null,
            'teacher'         => $teacher['name']
        ];
      });
    });

    return $result;
  }

  /**
   * @param int $date
   * @return Date
   */
  protected function getDate($date) {
    return Date::createDate(floor($date / 10000), floor($date / 100) % 100, $date % 100);
  }

  /**
   * @param int $date
   * @param int $time
   * @return Carbon
   */
  protected function getDateTime($date, $time) {
    return Carbon::create(floor($date / 10000), floor($date / 100) % 100, $date % 100, floor($time / 100), $time % 100, 0);
  }

}