<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/helpers.php");
require_once(__DIR__ . "/response.php");
require_once(__DIR__ . "/request.php");
require_once(__DIR__ . "/config.php");

// Models
require_once(__DIR__ . "/lib/Model.php");
require_once(__DIR__ . "/lib/SkladkaModel.php");
require_once(__DIR__ . "/lib/SkladkaTypModel.php");
require_once(__DIR__ . "/lib/SkladkaTypCrossModel.php");

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
        ? Response::getJson($skladkaModel->getPaginationDataComplex()) 
        : Response::getJson($skladkaModel->getAllComplex())
      ;
    break;
    case "skladky-vsetky-simple":
      $skladkaModel = new SkladkaModel();

      // POST data for FILTERING
      $postData = Request::getPostData();

      $data = [];
      if (isset($postData["filter"])) {
        $data = Request::getParam("pagination") 
          ? $skladkaModel->getPaginationDataFiltered(
              json_decode($postData["filter"], TRUE)
            )
          : $skladkaModel->getAllFiltered(
              json_decode($postData["filter"], TRUE)
            )
        ;
      } else {
        $data = Request::getParam("pagination") 
          ? $skladkaModel->getPaginationData()
          : $skladkaModel->getAll()
        ;
      }

      echo Response::getJson([
        "status" => "success",
        "data" => $data
      ]); 
    break;
    case "skladky-typy":
      $skladkaTypModel = new SkladkaTypModel();

      echo Response::getJson(
        $skladkaTypModel->getAllOrderBy("id", "ASC")
      );
    break;
    case "skladka":
      $skladkaModel = new SkladkaModel();

      if (!Request::getParam("id")) {
        Response::throwException("Unknown ID for skladka");
      }

      if (!Request::getParam("type")) {
        Response::throwException("Unknown type for skladka");
      }

      if (!is_numeric(Request::getParam("id"))) {
        Response::throwException("ID for skladka must be type of INT");
      }

      echo Response::getJson(
        $skladkaModel->getByIdComplex(
          (int)Request::getParam("id"),
          (int)Request::getParam("type")
        )
      );
    break;
    case "nahlasit":
      $postData = Request::getPostData();

      if (empty($postData)) Response::throwException("Data are empty");
      if (!isset($postData["choosenTypes"])) Response::throwException("Choosen types do not exits");
      if ($postData["choosenTypes"] == "") Response::throwException("Choosen Types are empty");
      if (!isset($postData["lat"])) Response::throwException("Lat not set");
      if (!isset($postData["lng"])) Response::throwException("Lng not set");

      $skladkaModel = new SkladkaModel();

      $insertedIdSkladka = $skladkaModel->insert([
        "nazov" => uniqid(),
        "okres" => "TODO",
        "obec" => "TODO",
        "rok_zacatia" => Date("Y-m-d"),
        "typ" => 2,
        "lat" => (float)$postData["lat"],
        "lng" => (float)$postData["lng"]
      ]); 
      
      $skladkaTypyCrossModel = new SkladkaTypCrossModel();

      $usedTypes = explode(",", $postData["choosenTypes"]);
      foreach ($usedTypes as $usedType) {
        $skladkaTypyCrossModel->insert([
          "id_skladka" => $insertedIdSkladka,
          "id_skladka_typ" => $usedType
        ]);
      }

      echo Response::getJson([
        "status" => "success",
        "insertedId" => $insertedIdSkladka
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