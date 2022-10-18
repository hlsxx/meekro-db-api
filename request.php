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
    return 
      isset($_POST) && !empty($_POST) 
      ? $_POST 
      : json_decode(file_get_contents("php://input"), TRUE)
    ;
  }

}