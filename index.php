<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// Common
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
require_once(__DIR__ . "/lib/UnknownUserModel.php");

DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;
DB::$encoding = 'utf8mb4_general_ci'; 

// Logs
$logInfo = new Monolog\Logger('MeekroAPI-Log-System');
$logInfo->pushHandler(
  new Monolog\Handler\StreamHandler(__DIR__ . '/logs/info.log', Monolog\Logger::INFO)
);

$logError = new Monolog\Logger('MeekroAPI-Log-System');
$logError->pushHandler(
  new Monolog\Handler\StreamHandler(__DIR__ . '/logs/error.log', Monolog\Logger::ERROR)
);

try {

  $logInfo->info("REQUEST from {$_SERVER['REMOTE_ADDR']}");

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
              Response::getArray($postData["filter"])
            )
          : $skladkaModel->getAllFiltered(
              Response::getArray($postData["filter"])
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

      echo Response::getJson([
        "status" => "success",
        "data" => $skladkaModel->getByIdComplex(
          (int)Request::getParam("id"),
          (int)Request::getParam("type")
        )
      ]);
    break;
    case "skladka-by-coors":
      $skladkaModel = new SkladkaModel();

      $postData = Request::getPostData();

      if (!isset($postData["lat"]) || empty($postData["lat"])) {
        Response::throwException("Param: lat does not exists or is empty");
      }

      if (!isset($postData["lng"]) || empty($postData["lng"])) {
        Response::throwException("Param: lng does not exists or is empty");
      }

      echo Response::getJson([
        "status" => "success",
        "data" => $skladkaModel->getByCoorsComplex(
          (float)$postData["lat"],
          (float)$postData["lng"]
        )
      ]);
    break;
    case "nahlasit":
      $postData = Request::getPostData();

      if (empty($postData)) Response::throwException("Data are empty");
      if (!isset($postData["choosenTypes"])) Response::throwException("Error: Parameter choosenTypes je prazdny");
      if ($postData["choosenTypes"] == "") Response::throwException("Vyberte typ");
      if (!isset($postData["lat"])) Response::throwException("Lat not set");
      if (!isset($postData["lng"])) Response::throwException("Lng not set");

      $skladkaModel = new SkladkaModel();

      $uniqueId = uniqid();

      $insertedIdSkladka = $skladkaModel->insert([
        "nazov" => "{$uniqueId}_nazov",
        "okres" => "{$uniqueId}_okres",
        "obec" => "{$uniqueId}_obec",
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
          "id_skladka_typ" => $usedType,
          "pocet_potvrdeni" => 1
        ]);
      }

      echo Response::getJson([
        "status" => "success",
        "insertedId" => $insertedIdSkladka
      ]);  
    break;
    case "potvrdit":
      $postData = Request::getPostData();

      //$deviceUID = $postData["uid"];
      $idSkladka = (int)$postData["idSkladka"];

      $skladkaModel = new SkladkaModel(); 

      $currentSkladka = $skladkaModel->getById($idSkladka);
      
      $skladkaModel->update([
        "pocet_nahlaseni" => (int)$currentSkladka["pocet_nahlaseni"] + 1
      ], $idSkladka);

      echo Response::getJson([
        "status" => "success"
      ]);  
    break;
    case "potvrdit-skladku-typy":
      $postData = Request::getPostData();

      $skladkaTypCrossModel = new SkladkaTypCrossModel();

      $idSkladka = $postData["idSkladka"];
      $choosenTypes = $postData["choosenTypes"];

      $usedTypes = explode(",", $choosenTypes);

      $newTypesAdded = [];
      foreach ($usedTypes as $usedType) {
        $skladkaTypCrossData = DB::queryFirstRow("
          SELECT 
            * 
          FROM {$skladkaTypCrossModel->tableName} 
          WHERE id_skladka = %i AND id_skladka_typ = %i
        ", (int)$idSkladka, (int)$usedType);

        // If type doesnt exists just add him, else update pocet_potvrdeni
        if ($skladkaTypCrossData === NULL) {
          $insertedType = $skladkaTypCrossModel->insert([
            "id_skladka" => (int)$idSkladka,
            "id_skladka_typ" => (int)$usedType,
            "pocet_potvrdeni" => 1
          ]);

          $newTypesAdded[] = (int)$usedType;
        } else {
          $skladkaTypCrossModel->update([
            "pocet_potvrdeni" => (int)$skladkaTypCrossData["pocet_potvrdeni"] + 1
          ], (int)$skladkaTypCrossData["id"]);
        }
      }

      echo Response::getJson([
        "status" => "success",
        "new_types" => Response::getJson($newTypesAdded)
      ]); 
    break;
    case "vygeneruj-uid":
      $unknownUserModel = new UnknownUser();

      $insertedUnknownUserId = $unknownUserModel->insert([
        "uid" => uniqid()
      ]);

      $unknownUser = $unknownUserModel->getById($insertedUnknownUserId);

      echo Response::getJson([
        "status" => "success",
        "unknownUserUID" => $unknownUser["uid"]
      ]); 
    break;
    default:
      Response::throwException("Page doesnt exists");
    break;

  }
} catch(\Exception $e) {
  $logError->error($e->getMessage());
  echo Response::getErrorJson($e);
}

?>