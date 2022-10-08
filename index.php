<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once("vendor/autoload.php");
require_once("helpers.php");
require_once("config.php");

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'dialite';
DB::$encoding = 'utf8mb4_general_ci'; 

try {
  // Check parameter PAGE
  if (!isset($_GET["page"])) throw new Exception("Unknown page to load");

  switch ($_GET["page"]) {
    case "skladky-vsetky":
      if (isset($_GET["pagination"])) {
        echo Helper::getPaginationData();
      } else {
        echo json_encode(DB::query("SELECT * FROM ucm_skladky"));
      }
    break;
    case "skladka":
      // Check parameter id
      if (!isset($_GET["id"])) throw new Exception("Unknown ID for skladka");
      // Check if is number
      if (!is_numeric($_GET["id"])) throw new Exception("ID for skladka must be type of INT");

      echo json_encode(DB::query("SELECT * FROM ucm_skladky WHERE id = %d", $_GET["id"]));
    break;
    case "nahlasit":
      $postData = Helper::getPostData();
    break;
  }
} catch(\Exception $e) {
  echo json_encode([
    "error" => "Error",
    "message" => $e->getMessage()
  ]);
}

?>