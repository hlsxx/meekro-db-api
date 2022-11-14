<?php

class Debug {

  public static function get($data) {
    echo Response::getJson([
      'status' => 'success',
      'data' => $data
    ]); 

    exit();
  }

}