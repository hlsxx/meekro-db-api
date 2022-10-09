<?php

class Request {

  public static function getParam(string $paramName) {
    return isset($_GET[$paramName]) ? $_GET[$paramName] : FALSE;
  }

}