<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// Common
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/response.php');
require_once(__DIR__ . '/request.php');
require_once(__DIR__ . '/config.php');

// Models
require_once(__DIR__ . '/lib/Model.php');
require_once(__DIR__ . '/lib/SkladkaModel.php');
require_once(__DIR__ . '/lib/SkladkaTypModel.php');
require_once(__DIR__ . '/lib/SkladkaTypCrossModel.php');
require_once(__DIR__ . '/lib/UnknownUserModel.php');
require_once(__DIR__ . '/lib/SkladkaPotvrdenieModel.php');
require_once(__DIR__ . '/lib/SkladkaUnknownUserModel.php');

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

  if (!Request::getParam('page')) {
    Response::throwException('GET param: {page} not set');
  }

  switch (Request::getParam('page')) {
    case 'skladky-vsetky': // GET
      $skladkaModel = new SkladkaModel();

      echo Request::getParam('pagination') 
        ? Response::getJson($skladkaModel->getPaginationDataComplex()) 
        : Response::getJson($skladkaModel->getAllComplex())
      ;
    break;
    case 'skladky-vsetky-simple': // GET | POST for filter
      $postData = Request::getPostData();

      $skladkaModel = new SkladkaModel();

      $data = [];
      if (isset($postData['filter'])) {
        $data = Request::getParam('pagination') 
          ? $skladkaModel->getPaginationDataFiltered(
              Response::getArray($postData['filter'])
            )
          : $skladkaModel->getAllFiltered(
              Response::getArray($postData['filter'])
            )
        ;
      } else {
        $data = Request::getParam('pagination') 
          ? $skladkaModel->getPaginationData()
          : $skladkaModel->getAll()
        ;
      }

      echo Response::getJson([
        'status' => 'success',
        'data' => $data
      ]); 
    break;
    case 'skladky-typy': // GET
      $skladkaTypModel = new SkladkaTypModel();

      echo Response::getJson(
        $skladkaTypModel->getAllOrderBy('id', 'ASC')
      );
    break;
    case 'skladka': // GET
      Request::validatePostParam('id');
      Request::validatePostParam('type');

      $skladkaModel = new SkladkaModel();

      echo Response::getJson([
        'status' => 'success',
        'data' => $skladkaModel->getByIdComplex(
          (int)Request::getParam('id'),
          (int)Request::getParam('type')
        )
      ]);
    break;
    case 'skladka-by-coors': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('lat');
      Request::validatePostParam('lng');

      $skladkaModel = new SkladkaModel();

      echo Response::getJson([
        'status' => 'success',
        'data' => $skladkaModel->getByCoorsComplex(
          (float)$postData['lat'],
          (float)$postData['lng']
        )
      ]);
    break;
    case 'nahlasit': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('choosenTypes');
      Request::validatePostParam('lat');
      Request::validatePostParam('lng');
      Request::validatePostParam('uid');

      if ($postData['choosenTypes'] == '') Response::throwException('Vyberte typ');

      $skladkaModel = new SkladkaModel();

      $uniqueId = uniqid();

      $insertedIdSkladka = $skladkaModel->insert([
        'nazov' => '{$uniqueId}_nazov',
        'okres' => '{$uniqueId}_okres',
        'obec' => '{$uniqueId}_obec',
        'rok_zacatia' => Date('Y-m-d'),
        'typ' => 2,
        'lat' => (float)$postData['lat'],
        'lng' => (float)$postData['lng']
      ]); 

      $skladkaUnknownUserModel = new SkladkaUnknownUserModel();

      $skladkaUnknownUserModel->insert([
        'id_skladka' => $insertedIdSkladka,
        'unknown_user_uid' => $postData['uid']
      ]);
      
      $skladkaTypyCrossModel = new SkladkaTypCrossModel();

      $usedTypes = explode(',', $postData['choosenTypes']);
      foreach ($usedTypes as $usedType) {
        $skladkaTypyCrossModel->insert([
          'id_skladka' => $insertedIdSkladka,
          'id_skladka_typ' => $usedType,
          'pocet_potvrdeni' => 1
        ]);
      }

      echo Response::getJson([
        'status' => 'success',
        'insertedId' => $insertedIdSkladka
      ]);  
    break;
    case 'potvrdit': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('idSkladka');
      Request::validatePostParam('uid');

      $idSkladka = (int)$postData['idSkladka'];

      $skladkaModel = new SkladkaModel(); 

      $currentSkladka = $skladkaModel->getById($idSkladka);
      
      $skladkaModel->update([
        'pocet_nahlaseni' => (int)$currentSkladka['pocet_nahlaseni'] + 1
      ], $idSkladka);

      $skladkaPotvrdenieModel = new SkladkaPotvrdenieModel();

      $skladkaPotvrdenieModel->insert([
        'id_skladka' => $idSkladka,
        'unknown_user_uid' => $postData['uid']
      ]);

      echo Response::getJson([
        'status' => 'success'
      ]);  
    break;
    case 'potvrdil-som': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('idSkladka');
      Request::validatePostParam('uid');

      $skladkaPotvrdenieModel = new SkladkaPotvrdenieModel();

      $data = DB::queryFirstRow('
        SELECT
          *
        FROM {$skladkaPotvrdenieModel->tableName}
        WHERE id_skladka = %i AND unknown_user_uid = %s
      ', (int)$idSkladka, (string)$uid);

      echo Response::getJson([
        'status' => 'success',
        'confirmed' => !empty($data) ? true : false
      ]);
    break;
    case 'potvrdit-skladku-typy': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('idSkladka');
      Request::validatePostParam('choosenTypes');

      $skladkaTypCrossModel = new SkladkaTypCrossModel();

      $usedTypes = explode(',', $choosenTypes);

      $newTypesAdded = [];
      foreach ($usedTypes as $usedType) {
        $skladkaTypCrossData = DB::queryFirstRow('
          SELECT 
            * 
          FROM {$skladkaTypCrossModel->tableName} 
          WHERE id_skladka = %i AND id_skladka_typ = %i
        ', (int)$idSkladka, (int)$usedType);

        // If type doesnt exists just add him, else update pocet_potvrdeni
        if ($skladkaTypCrossData === NULL) {
          $insertedType = $skladkaTypCrossModel->insert([
            'id_skladka' => (int)$idSkladka,
            'id_skladka_typ' => (int)$usedType,
            'pocet_potvrdeni' => 1
          ]);

          $newTypesAdded[] = (int)$usedType;
        } else {
          $skladkaTypCrossModel->update([
            'pocet_potvrdeni' => (int)$skladkaTypCrossData['pocet_potvrdeni'] + 1
          ], (int)$skladkaTypCrossData['id']);
        }
      }

      echo Response::getJson([
        'status' => 'success',
        'new_types' => Response::getJson($newTypesAdded)
      ]); 
    break;
    case 'vygeneruj-uid': // GET
      $unknownUserModel = new UnknownUserModel();

      $insertedUnknownUserId = $unknownUserModel->insert([
        'uid' => uniqid(),
        'created_at' => date('Y-m-d H:i:s', time())
      ]);

      $unknownUser = $unknownUserModel->getById($insertedUnknownUserId);

      echo Response::getJson([
        'status' => 'success',
        'unknownUserUID' => $unknownUser['uid']
      ]); 
    break;
    case 'zaznamenat-aktivitu': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('uid');

      $unknownUserModel = new UnknownUserModel();

      DB::update(
        $unknownUserModel->tableName, 
        [
          'last_login' => date('Y-m-d H:i:s', time())
        ], 
        'uid = %s',
        $postData['uid']
      );
    break;
    default:
      Response::throwException('PAGE: {' . Request::getParam('page') . '} doesnt exists');
    break;

  }
} catch(\Exception $e) {
  $logError->error($e->getMessage());
  echo Response::getErrorJson($e);
}

?>