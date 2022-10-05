<?php

class Helper {

  static private int $itemsPerPage = 6;

  private static function getOffset() {
    return ($_GET["pagination"] - 1) * self::$itemsPerPage;
  }

  public static function getPaginationData() {
    echo json_encode(
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

}