<?php

require_once("vendor/autoload.php");

if (!isset($_GET["page"])) exit("Page is not defined!!!");

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'ucm_skladky';
DB::$encoding = 'utf8mb4_general_ci'; 

switch ($_GET["page"]) {
  case "skladky-vsetky":
    echo json_encode(DB::query("SELECT * FROM skladky"));
  break;
}

?>