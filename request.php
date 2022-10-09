<?php

class Request {

  /**
   * @param string $paramName
   * @return any
   */
  public static function getParam(string $paramName) {
    return isset($_GET[$paramName]) ? $_GET[$paramName] : FALSE;
  }

  /**
   * @return array post data
   */
  public static function getPostData() {
    return isset($_POST) ? $_POST : file_get_contents("php://input");
  }

}