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

  public static function getSkladkaTyp(int $typSkladkyCislo, int $pocetPotvrdeni) {
    $typ = "";

    switch ($typSkladkyCislo) {
      case 1:
        $typ = "papier";
      break;
      case 2:
        $typ = "plast";
      break;
      case 3:
        $typ = "olej";
      break;
      case 4:
        $typ = "sklo";
      break;
      case 5:
        $typ = "elektro";
      break;
      case 6:
        $typ = "zmiesane";
      break;
      case 7:
        $typ = "vlastne";
      break;
      default:
        $typ = "zmiesane";
      break;
    }

    return [
      $typ => $pocetPotvrdeni
    ];
  }
}