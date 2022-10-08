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

  /**
   * @return ARRAY post data
   */
  public static function getPostData() {
    return isset($_POST) ? $_POST : file_get_contents("php://input");
  }

}