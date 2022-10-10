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
        ORDER BY id DESC
        LIMIT %d, %d
      ", 
        self::getOffset(),
        self::$itemsPerPage
      )
    );
  }

  public static function getSkladkaTyp(int $typSkladkyCislo, string $typSkladky) {
    switch ($typSkladkyCislo) {
      case 1:
        
      break;
      case 2:
      break;
      case 3:
      break;
      case 4:
      break;
      case 5:
      break;
      case 6:
      break;
      case 7:
      break;
      default:
      break;
    }
  }
}