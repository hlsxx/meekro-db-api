<?php

class Common {

  public static function androidOrIos() {
    global $bride;

    if (Request::getParamIsset('device_type')) {
      $getData = Request::getGetData();

      $devicesLogs = $bride->initModel('devices_logs');
      $devicesLogs->insert([
        'device_type' => $getData['device_type'],
        'page' => $getData['page'],
        'created_at' => date('Y-m-d H:i:s')
      ]);
    }
  }

}