<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once("vendor/autoload.php");
require_once("helpers.php");

if (!isset($_GET["page"])) exit("Page is not defined!!!");

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'dialite';
DB::$encoding = 'utf8mb4_general_ci'; 

switch ($_GET["page"]) {
  case "skladky-vsetky":
    if (isset($_GET["pagination"])) {
      echo getPaginationData();
    } else {
      echo json_encode(DB::query("SELECT * FROM ucm_skladky"));
    }
  break;
}

?>