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

}