<?php

class Helper {

  static public int $itemsPerPage = 6;

  /**
   * @return INT pagination offset
   */
  public static function getOffset() : int {
    return ($_GET["pagination"] - 1) * self::$itemsPerPage;
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