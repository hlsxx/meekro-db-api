<?php

class Request {

  /**
   * @param string $paramName
   * 
   * @return any
   */
  public static function getParam(string $paramName) {
    return isset($_GET[$paramName]) ? $_GET[$paramName] : FALSE;
  }

  /**
   * @return array post data
   */
  public static function getPostData(): array {
    return 
      isset($_POST) && !empty($_POST) 
      ? $_POST 
      : json_decode(file_get_contents("php://input"), TRUE)
    ;
  }

  /**
   * @param string POST param name
   * 
   * @return bool exists or no
   */
  public static function postParamIsset(string $paramName): bool {
    return isset($_POST[$paramName]);
  }

  /**
   * @param string POST param name
   * 
   * @return bool exists or no
   */
  public static function getParamIsset(string $paramName): bool {
    return isset($_GET[$paramName]);
  }

  /**
   * @param string POST param name
   * 
   * @return void
   */
  public static function validatePostParam(string $paramName): void {
    if (self::postParamIsset($paramName) == false) {
      throw new Exception("POST param: {{$paramName}} does not exists.");
    }
  }

  /**
   * @param string GET param name
   * 
   * @return void
   */
  public static function validateGetParam(string $paramName): void {
    if (self::getParamIsset($paramName) == false) {
      throw new Exception("GET param: {{$paramName}} does not exists.");
    }
  }

}