<?php

namespace App\Services;

interface RoomService {

  /**
   * Replace room occupation with updated data from Untis
   */
  public function loadRoomOccupations();

}
