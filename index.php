<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once("vendor/autoload.php");
require_once("helpers.php");
require_once("response.php");
require_once("request.php");
require_once("config.php");

require_once("lib/Model.php");
require_once("lib/SkladkaModel.php");

DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;
DB::$encoding = 'utf8mb4_general_ci'; 

try {
  if (!Request::getParam("page")) {
    Response::throwException("Unknown page to load");
  }

  switch (Request::getParam("page")) {
    case "skladky-vsetky":
      $skladkaModel = new SkladkaModel();

      echo Request::getParam("pagination") 
        ? Helper::getPaginationData() 
        : Response::getJson($skladkaModel->getAll())
      ;
    break;
    case "skladka":
      $skladkaModel = new SkladkaModel();

      if (!Request::getParam("id")) {
        Response::throwException("Unknown ID for skladka");
      }

      if (!is_numeric(Request::getParam("id"))) {
        Response::throwException("ID for skladka must be type of INT");
      }

      echo Response::getJson(
        $skladkaModel->getById((int)Request::getParam("id"))
      );
    break;
    case "nahlasit":
      $postData = Request::getPostData();

      if (empty($postData)) Response::throwException("Data are empty");

      $skladkaModel = new SkladkaModel();

      echo Response::getJson([
        "status" => "success",
        "insertedId" => $skladkaModel->insert([
          "nazov" => "xxx",
          "okres" => "xxx",
          "obec" => "xx",
          "rok_zacatia" => Date("Y-m-d"),
          "typ" => 2,
          "lat" => $postData["lat"],
          "lng" => $postData["lng"]
        ])
      ]);          
    break;
    default:
      Response::throwException("Page doesnt exists");
    break;
  }
} catch(\Exception $e) {
  echo Response::getErrorJson($e);
}

?>