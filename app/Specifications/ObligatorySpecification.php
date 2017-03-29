<?php

namespace App\Specifications;

interface ObligatorySpecification {

  /**
   * @return int
   */
  public function getSubject();

  /**
   * @return int[]
   */
  public function getGroups();

}