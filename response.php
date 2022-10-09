<?php

class Response {

  public static function getJson(array $dataToReturn) {
    return json_encode($dataToReturn);
  }

  public static function getErrorJson(Exception $e) {
    return json_encode([
      "error" => "Error",
      "message" => $e->getMessage()
    ]);
  } 

  public static function throwException(string $errorMessage) {
    throw new Exception($errorMessage);
  }

}