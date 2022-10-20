<?php

class Response {

  /**
   * @param array $dataToReturn
   * @return json data
   */
  public static function getJson(array $dataToReturn) {
    return json_encode($dataToReturn);
  }

  /**
   * @param array|string $dataToReturn
   * @return array data
   */
  public static function getArray($dataToReturn) {
    return 
      is_string($dataToReturn)
      ? json_decode($dataToReturn, TRUE)
      : (array)$dataToReturn
    ;
  }

  /**
   * @param array $dataToReturn
   * @return json data
   */
  public static function get(array $dataToReturn) {
    return json_encode($dataToReturn);
  }

  /**
   * @param exception $e
   * @return json error 
   */
  public static function getErrorJson(Exception $e) {
    return json_encode([
      "status" => "error",
      "message" => $e->getMessage()
    ]);
  } 

  /**
   * @param string $errorMessage
   * @return void
   */
  public static function throwException(string $errorMessage) {
    throw new Exception($errorMessage);
  }

}