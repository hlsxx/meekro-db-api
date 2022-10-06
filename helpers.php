<?php

class Helper {

  static private int $itemsPerPage = 6;

  /**
   * @return INT pagination offset
   */
  private static function getOffset() : int {
    return ($_GET["pagination"] - 1) * self::$itemsPerPage;
  }

  /**
   * @return JSON data from ucm_skladky
   */
  public static function getPaginationData() : string {
    return json_encode(
      DB::query("
        SELECT 
          * 
        FROM ucm_skladky
        LIMIT %d, %d
      ", 
        self::getOffset(),
        self::$itemsPerPage
      )
    );
  }

  public static function getCoordinates() {
    //var_dump(file_get_contents("http://maps.google.com/maps/api/geocode/json?address=svrbice"));
  }

}