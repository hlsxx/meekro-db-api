<?php

class Request {

  /**
   * @param string $paramName
   * @return any
   */
  public static function getParam(string $paramName) {
    return isset($_GET[$paramName]) ? $_GET[$paramName] : FALSE;
  }

}