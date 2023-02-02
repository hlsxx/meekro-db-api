<?php

namespace Models;

class SkladkyPotvrdenia {

  private stdClass $model;

  public function __construct(stdClass $bride) {
    $this->model = $bride->initModel('skladky_potvrdenia');
  }

}