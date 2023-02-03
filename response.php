<?php

class Response {

  /**
   * @param array $dataToReturn
   * @return json data
   */
  public static function getJson(array $dataToReturn): string {
    return json_encode($dataToReturn);
  }

  /**
   * @param array $dataToReturn
   * @return void
   */
  public static function get(array $dataToReturn): void {
    echo self::getJson([
      'status' => 'success',
      'data' => $dataToReturn
    ]);

    exit;
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
   * @param exception $e
   * @return json error 
   */
  public static function getErrorJson(Exception $e) {
    return json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);
  } 

  /**
   * @param string $errorMessage
   * @return void
   */
  public static function throwException(string $errorMessage) {
    throw new Exception($errorMessage);
  }

   /**
   * @param string $warningMessage
   * @return string 
   */
  public static function throwWarning(string $warningMessage) {
    echo self::getJson([
      'status' => 'warning',
      'message' => $warningMessage
    ]);

    exit;
  }

  /**
   * @param string $errorMessage
   * @param array $data
   * @return void
   */
  public static function throwExceptionWithData(string $errorMessage, array $data) {
    return json_encode([
      'status' => 'error',
      'message' => $errorMessage,
      'data' => $data
    ]);

    exit;
  }

}