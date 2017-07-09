<?php

namespace App\Services\Implementation;

use App\Helpers\Date;
use App\Helpers\DateRange;
use App\Services\WebUntisService;
use Carbon\Carbon;
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
          'id'    => intval($item['studentid'], 10),
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