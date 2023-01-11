<?php

abstract class TypSkladkyEnum {
  public static array $typy = [
    1 => "papier",
    2 => "plast",
    3 => "olej",
    4 => "sklo",
    5 => "elektro",
    6 => "vlastne",
    7 => "zmiesane"
  ];
} 

class Helper {

  static public int $itemsPerPage = 6;

  /**
   * @return INT pagination offset
   */
  public static function getOffset() : int {
    return ($_GET["pagination"] - 1) * self::$itemsPerPage;
  }

  /**
   * @param int $typSkladkyCislo
   * @param int $pocetPotvrdeni
   * @return array typ => pocet (sklo => 13)
   */
  public static function getSkladkaTyp(int $typSkladkyCislo, int $pocetPotvrdeni) {
    return [
      TypSkladkyEnum::$typy[$typSkladkyCislo] => $pocetPotvrdeni
    ];
  }

  /**
   * Calculates the great-circle distance between two points, with
   * the Haversine formula.
   * @param float $latitudeFrom Latitude of start point in [deg decimal]
   * @param float $longitudeFrom Longitude of start point in [deg decimal]
   * @param float $latitudeTo Latitude of target point in [deg decimal]
   * @param float $longitudeTo Longitude of target point in [deg decimal]
   * @param float $earthRadius Mean earth radius in [m]
   * @return float Distance between points in [m] (same as earthRadius)
   */
  public static function getDistanceFromLatLonInKm(
    $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000
  ) {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
      cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)))
    ;

    return ($angle * $earthRadius) / 1000;
  }

  public static function deleteSpaces(string $string): string {
    return str_replace(' ', '', $string);
  }

  public static function getDateMinusOneDay(string $datetime): string  {
    return self::incDecOneDay($datetime, "-");
  }

  public static function getDatePlusOneDay(string $datetime): string {
    return self::incDecOneDay($datetime, "+");
  }

  public static function incDecOneDay(string $datetime, string $operator): string {
    $newDateTime = new DateTime($datetime);
    $newDateTime->modify("{$operator}1 day");

    return $newDateTime->format('Y-m-d H:i:s');
  }
  
}