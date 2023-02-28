<?php

class Common {

  public static function androidOrIos(): void {
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

    if (self::getDeviceType() == 1) {
      if (in_array(Request::getParam('screen'), ANDROID_DISABLED_APP_SCREENS)) {
        Response::throwException('Táto obrazovka nie je dostupná');
      }
    } else if(self::getDeviceType() == 2) {
      if (in_array(Request::getParam('screen'), ANDROID_DISABLED_APP_SCREENS)) {
        Response::throwException('Táto obrazovka nie je dostupná');
      }
    }
  }


  public static function getDeviceType(): int {
    return (int)Request::getParam('device_type');
  }

  public static function securiter(): void {
    /*if (DEBUG_MODE === false) {
      $getData = Request::getGetData();

      if (!isset($getData['hash'])) self::get405('permission');
      if (in_array($getData['hash'], ['ucm70'])) self::get405('permission');
    }*/

    if (DISABLE_APP === true) {
      Response::throwException('Aplikácia je dočasne pozastavenená');
    }
  }

  public static function get405(string $type): void {
    if ($type == 'permission') header("HTTP/1.0 405 Not permitted", true, 405); 
    else header("HTTP/1.0 405 Method Not Allowed");

    exit;
  }

  public static function reverseThrotle(): string {
    $st = str_replace(chr(120), chr(108), GOOGLE_API_KEY);
    return str_replace(chr(104), chr(97), $st);
  }

  public static function geocoding(float $lat, float $lng): array {
    if (GEOCODING_ENABLED === true) {
      $jsonData = file_get_contents(
        "https://maps.google.com/maps/api/geocode/json?latlng={$lat},{$lng}&key=" . self::reverseThrotle()
      );

      $arrayData = json_decode($jsonData, true);
      $fullDataAddress = $arrayData['results'][0]['address_components'];

      $returnAddress = [
        'adresa' => '',
        'obec' => '',
        'okres' => '',
        'kraj' => ''
      ];
      
      foreach ($fullDataAddress as $address) {
        foreach ($address['types'] as $type) {
          if ($type == 'premise') {
            $returnAddress['adresa'] .= $address['long_name'];
            break;
          }

          if ($type == 'street_number') {
            $returnAddress['adresa'] = $address['long_name'] . '/';
            break;
          }

          if ($type == 'locality') {
            $returnAddress['obec'] = $address['long_name'];
            break;
          }

          if ($type == 'administrative_area_level_2') {
            $returnAddress['okres'] = $address['long_name'];
            break;
          }

          if ($type == 'administrative_area_level_1') {
            $returnAddress['kraj'] = $address['long_name'];
            break;
          }
        }
      }

      //return $arrayData['results'][0]['address_components'];
      return $returnAddress;
    }

    return [];
  }

}