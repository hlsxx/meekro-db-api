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

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'dialite';
DB::$encoding = 'utf8mb4_general_ci'; 

try {
  // Check parameter PAGE
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

      // Check parameter id
      if (!Request::getParam("id")) {
        Response::throwException("Unknown ID for skladka");
      }

      // Check if is number
      if (!is_numeric(Request::getParam("id"))) {
        Response::throwException("ID for skladka must be type of INT");
      }

      echo Response::getJson(
        $skladkaModel->getById($_GET["id"])
      );
    break;
    case "nahlasit":
      $postData = Helper::getPostData();

      try {
        $skladkaModel = new SkladkaModel();
      } catch(Exception $e) {

      }
    break;
    default:
      Response::throwException("Page doesnt exists");
    break;
  }
} catch(\Exception $e) {
  echo Response::getErrorJson($e);
}

?>