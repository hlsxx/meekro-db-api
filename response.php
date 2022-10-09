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
   * @param exception $e
   * @return json error 
   */
  public static function getErrorJson(Exception $e) {
    return json_encode([
      "error" => "Error",
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