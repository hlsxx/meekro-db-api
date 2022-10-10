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
}