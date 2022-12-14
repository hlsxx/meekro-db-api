<?php

class Common {

  public static function androidOrIos() {
    global $bride;

    $checkPages = ['nahlasit', 'registracia-validacia', 'vycistit'];

    if (Request::getParamIsset('device_type')) {
      $getData = Request::getGetData();

      if (in_array($getData['page'], $checkPages)) {
        $devicesLogs = $bride->initModel('devices_logs');
        $devicesLogs->insert([
          'device_type' => $getData['device_type'],
          'page' => $getData['page'],
          'created_at' => date('Y-m-d H:i:s')
        ]);
      }
    }
  }

  public static function securiter() {
    if (DEBUG_MODE === false) {
      $getData = Request::getGetData();

      if (!isset($getData['hash'])) self::get405('permission');
      if (in_array($getData['hash'], ['ucm34'])) self::get405('permission');
    }
  }

  public static function get405(string $type) {
    if ($type == 'permission') header("HTTP/1.0 405 Not permitted", true, 405); 
    else header("HTTP/1.0 405 Method Not Allowed");

    exit;
  }

}