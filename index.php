<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');

// App
require_once(__DIR__ . '/app.php');

// Common
require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/helpers.php');
require_once(__DIR__ . '/response.php');
require_once(__DIR__ . '/request.php');
require_once(__DIR__ . '/config.php');

// Debug
require_once(__DIR__ . '/debug.php');

DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;

$bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);
$bride->tablePrefix('ucm');

// Models
require_once(__DIR__ . '/lib/Model.php');
require_once(__DIR__ . '/lib/SkladkaModel.php');
require_once(__DIR__ . '/lib/SkladkaTypModel.php');
require_once(__DIR__ . '/lib/SkladkaTypCrossModel.php');
require_once(__DIR__ . '/lib/UnknownUserModel.php');
require_once(__DIR__ . '/lib/SkladkaPotvrdenieModel.php');
require_once(__DIR__ . '/lib/SkladkaUnknownUserModel.php');
require_once(__DIR__ . '/lib/TokenModel.php');

// Mailer
require_once(__DIR__ . '/lib/Mailer.php');

// Logs
$logInfo = new Monolog\Logger('MeekroAPI-Log-System');
$logInfo->pushHandler(
  new Monolog\Handler\StreamHandler(__DIR__ . '/logs/info.log', Monolog\Logger::INFO)
);

$logError = new Monolog\Logger('MeekroAPI-Log-System');
$logError->pushHandler(
  new Monolog\Handler\StreamHandler(__DIR__ . '/logs/error.log', Monolog\Logger::ERROR)
);

// Test file
require_once(__DIR__ . '/test.php');

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
      $skladkaModel = new SkladkaModel();

      $data = [];
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $postData = Request::getPostData();

        Request::validatePostParam('filter');

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
    case 'skladky-vsetky-simple-mapa': // GET
      $skladkaModel = new SkladkaModel();

      Request::validateGetParam('zoom-level');

      $zoomLevel = Request::getParam('zoom-level');
      
      $data = $skladkaModel->getByZoomLevel((int)$zoomLevel);

      echo Response::getJson([
        'status' => 'success',
        'data' => $data
      ]); 
    break;
    case 'skladky-vsetky-complex': // GET | POST for filter
      $skladkaModel = new SkladkaModel();

      $data = [];
      if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $postData = Request::getPostData();

        Request::validatePostParam('filter');

        $data = Request::getParam('pagination') 
          ? $skladkaModel->getPaginationDataFilteredComplex(
              Response::getArray($postData['filter'])
            )
          : $skladkaModel->getAllFilteredComplex(
              Response::getArray($postData['filter'])
            )
        ;
      } else {
        $data = Request::getParam('pagination') 
          ? $skladkaModel->getPaginationDataComplex()
          : $skladkaModel->getAllComplex()
        ;
      }

      echo Response::getJson([
        'status' => 'success',
        'data' => $data
      ]); 
    break;
    case 'uzivatel-nahlasene-skladky': // GET
      $getData = Request::getGetData();

      Request::validateGetParam('uid');

      $skladkaModel = $bride->initModel('skladky');
      $unknownUserModel = $bride->initModel('unknown_users');

      $unknownUserData = $unknownUserModel->getByCustom('uid', $getData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');
      if ($unknownUserData['id_user'] == NULL) Response::throwException('You must be registered before');

      $skladkyData = $skladkaModel->query("
        SELECT 
          *
        FROM {model}
        WHERE id_unknown_user = %i
      ", (int)$unknownUserData['id']);

      echo Response::getJson([
        'status' => 'success',
        'data' => $skladkyData
      ]);
    break;
    case 'skladky-typy': // GET
      $skladkaTypModel = new SkladkaTypModel();

      echo Response::getJson([
        'status' => 'success',
        'data' => $skladkaTypModel->getAllOrderBy('id', 'ASC')
      ]);
    break;
    case 'skladka': // GET
      Request::validateGetParam('id');
      Request::validateGetParam('type');

      $skladkaModel = new SkladkaModel();

      echo Response::getJson([
        'status' => 'success',
        'data' => $skladkaModel->getById(
          (int)Request::getParam('id')
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

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $skladkaModel = new SkladkaModel();

      $uniqueId = uniqid();

      $insertedIdSkladka = $skladkaModel->insert([
        'nazov' => "{$uniqueId}_nazov",
        'okres' => "{$uniqueId}_okres",
        'obec' => "{$uniqueId}_obec",
        'rok_zacatia' => Date('Y-m-d'),
        'typ' => 2,
        'lat' => (float)$postData['lat'],
        'lng' => (float)$postData['lng'],
        'id_unknown_user' => (int)$unknownUserData['id']
      ]); 

      /* $skladkaUnknownUserModel = new SkladkaUnknownUserModel();

      $skladkaUnknownUserModel->insert([
        'id_skladka' => $insertedIdSkladka,
        'unknown_user_uid' => $postData['uid']
      ]);*/
      
      $skladkaTypyCrossModel = new SkladkaTypCrossModel();

      $usedTypes = explode(',', $postData['choosenTypes']);
      foreach ($usedTypes as $usedType) {
        $skladkaTypyCrossModel->insert([
          'id_skladka' => $insertedIdSkladka,
          'id_skladka_typ' => $usedType,
          'pocet_potvrdeni' => 1,
          'id_unknown_user' => (int)$unknownUserData['id']
        ]);
      }

      /** Images upload */
      if (!empty($_FILES)) {
        $filePath = FILES_DIR . '/nelegalne-skladky/' . $insertedIdSkladka;

        $galleryModel = $bride->initModel('gallery');
        $skladkyGalleryModel = $bride->initModel('skladky_gallery');

        foreach ($_FILES as $file) {
          if ($file['size'] > 0) {
            if (!file_exists($filePath)) {
              if (!mkdir($filePath, 0755, true)) {
                Response::throwException('Error with creating folder');
              }
            }

            $fileExtension = explode('.', $file['name']);

            if (!in_array(end($fileExtension), ['jpeg', 'jpg', 'png'])) {
              Response::throwException('Allowed format of images: jpeg, jpg, png');
            }

            $countExistingImages = count(scandir($filePath)) - 2;
            $newName = ($countExistingImages + 1) . '.' . end($fileExtension);

            $imagePath = $filePath . '/' . $newName;

            if (!move_uploaded_file($file['tmp_name'], $imagePath)) {
              Response::throwException('Error with images uploading');
            }

            $idGallery = $galleryModel->insert([
              'link' => $newName,
              'created_at' => date('Y-m-d H:i:s')
            ]);

            $skladkyGalleryModel->insert([
              'id_skladka' => (int)$insertedIdSkladka,
              'id_gallery' => (int)$idGallery,
              'id_unknown_user' => (int)$unknownUserData['id']
            ]);
          }
        }
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

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $idSkladka = (int)$postData['idSkladka'];

      $skladkaModel = new SkladkaModel(); 

      $currentSkladka = $skladkaModel->getById($idSkladka);
      
      $skladkaModel->update([
        'pocet_nahlaseni' => (int)$currentSkladka['pocet_nahlaseni'] + 1
      ], $idSkladka);

      $skladkaPotvrdenieModel = $bride->initModel('skladky_potvrdenia');
      
      $skladkaPotvrdenieModel->insert([
        'id_skladka' => $idSkladka,
        'id_unknown_user' => (int)$unknownUserData['id']
      ]);

      echo Response::getJson([
        'status' => 'success'
      ]);  
    break;
    case 'vycistit': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('idSkladka');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $idSkladka = (int)$postData['idSkladka'];

      $skladkaModel = $bride->initModel('skladky');

      $currentSkladka = $skladkaModel->getById($idSkladka);
      
      $skladkaModel->update([
        'existujuca' => 0
      ], $idSkladka);

      $skladkaVycisteneModel = $bride->initModel('skladky_vycistene');
      
      $skladkaVycisteneModel->insert([
        'id_skladka' => $idSkladka,
        'id_unknown_user' => (int)$unknownUserData['id'],
        'created_at' => date('Y-m-d H:i:s')
      ]);

      echo Response::getJson([
        'status' => 'success'
      ]);  
    break;
    case 'potvrdil-som': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('idSkladka');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $skladkaPotvrdenieModel = new SkladkaPotvrdenieModel();

      $data = DB::queryFirstRow("
        SELECT
          *
        FROM {$skladkaPotvrdenieModel->tableName}
        WHERE id_skladka = %i AND unknown_user_id = %i
      ", (int)$postData['idSkladka'], (int)$unknownUserData['id']);

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

      $usedTypes = explode(',', $postData['choosenTypes']);

      $newTypesAdded = [];
      foreach ($usedTypes as $usedType) {
        $skladkaTypCrossData = DB::queryFirstRow("
          SELECT 
            * 
          FROM {$skladkaTypCrossModel->tableName} 
          WHERE id_skladka = %i AND id_skladka_typ = %i
        ", (int)$postData['idSkladka'], (int)$usedType);

        // If type doesnt exists just add him, else update pocet_potvrdeni
        if ($skladkaTypCrossData === NULL) {
          $insertedType = $skladkaTypCrossModel->insert([
            'id_skladka' => (int)$postData['idSkladka'],
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

      $uid = uniqid();
      $insertedUnknownUserId = $unknownUserModel->insert([
        'uid' => $uid,
        'created_at' => date('Y-m-d H:i:s', time())
      ]);

      if ($insertedUnknownUserId === false) Response::throwException('Unknown Error creating UID');

      echo Response::getJson([
        'status' => 'success',
        'unknownUserUID' => $uid
      ]); 
    break;
    case 'zaznamenat-aktivitu': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $unknownUserModel->update(
        [
          'last_login' => date('Y-m-d H:i:s', time())
        ],
        (int)$unknownUserData['id']
      );

      echo Response::getJson([
        'status' => 'success'
      ]); 
    break;
    case 'registracia': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('email');
      Request::validatePostParam('password');
      Request::validatePostParam('uid');

      if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
        Response::throwException('Incorrect email format');
      }

      if (strlen($postData['password']) < 8) {
        Response::throwException('Password musst have at least 8 symbols');
      }

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $userModel = $bride->initModel('users');
      $userAlreadyExists = $userModel->getByCustom('email', $postData['email']);

      if (!empty($userAlreadyExists)) Response::throwException('User email already exists');

      $createdAt = date('Y-m-d H:i:s');
      $idUser = $userModel->insert([
        'email' => $postData['email'],
        'password' => password_hash($postData['password'], PASSWORD_BCRYPT),
        'created_at' => $createdAt
      ]);

      $tokenNumber = (new TokenModel())->getTokenNumber();

      $tokenModel = $bride->initModel('tokens');
      $tokenModel->insert([
        'id_user' => (int)$idUser,
        'id_unknown_user' => (int)$unknownUserData['id'],
        'attempt' => 3,
        'type' => 1,
        'token_number' => $tokenNumber,
        'created_at' => $createdAt
      ]);

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'email' => $postData['email']
        ]
      ]);
    break;
    case 'prihlasenie': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('email');
      Request::validatePostParam('password');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
        Response::throwException('Incorrect email format');
      }

      $userModel = $bride->initModel('users');
      $userData = $userModel->getByCustom('email', $postData['email']);

      if (empty($userData)) Response::throwException('Email doesnt exists');

      if (!password_verify($postData['password'], $userData['password'])) {
        Response::throwException('Password is incorrect');
      }

      if ((bool)$userData['verified'] == false) Response::throwException('Account is not verified');

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'email' => $userData['email']
        ]
      ]);
    break;
    case 'registracia-validacia': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('token_number');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $tokenModel = $bride->initModel('tokens');

      $tokenData = DB::queryFirstRow("
        SELECT 
          *
        FROM {$tokenModel->tableName} 
        WHERE id_unknown_user = %i
        AND type = 1
      ", (int)$unknownUserData['id']);

      if (empty($tokenData)) Response::throwException('Token not exists for UID: ' . $postData['uid']);
      
      $timeToTokenValid = strtotime($tokenData['created_at'] . ' + 10 minute');

      if (date('Y-m-d H:i:s', $timeToTokenValid) < date('Y-m-d H:i:s')) {
        $tokenModel->delete($tokenData['id']);

        // TODO: SEND MAIL WITH NEW TOKEN
        $tokenNumber = (new TokenModel())->getTokenNumber();

        $tokenModel = $bride->initModel('tokens');
        $tokenModel->insert([
          'id_user' => (int)$tokenData['id_user'],
          'id_unknown_user' => (int)$unknownUserData['id'],
          'attempt' => 3,
          'type' => 1,
          'token_number' => $tokenNumber,
          'created_at' => date('Y-m-d H:i:s')
        ]);

        Response::throwException('The token has been expired. We sent you another one.');
      }

      if ((int)$tokenData['token_number'] == (int)$postData['token_number']) {
        $tokenModel->delete($tokenData['id']);

        $unknownUserModel->update([
          'id_user' => (int)$tokenData['id_user']
        ], (int)$unknownUserData['id']);

        $userModel = $bride->initModel('users');
        $userModel->update([
          'verified' => 1
        ], $tokenData['id_user']);

        echo Response::getJson([
          'status' => 'success',
          'message' => 'Successful verified'
        ]);
      } else {
        $tokenModel->update([
          'attempt' => (int)$tokenData['attempt'] - 1
        ], $tokenData['id']);

        Response::throwException('Code is invalid, try again');
      }
    break;
    case 'ucet-prehlad': // GET
      $getData = Request::getGetData();

      Request::validateGetParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $getData['uid']);

      if (empty($unknownUserData)) Response::throwException('Invalid UID');

      $skladkaModel = $bride->initModel('skladky');

      $skladkyData = $skladkaModel->query("
        SELECT 
          *
        FROM {model}
        WHERE id_unknown_user = %i
      ", (int)$unknownUserData['id']);

      $skladkaPotvrdeniaModel = $bride->initModel('skladky_potvrdenia');
      $skladkaPotvrdeniaData = $skladkaPotvrdeniaModel->query("
        SELECT 
          *
        FROM {model}
        WHERE id_unknown_user = %i
      ", (int)$unknownUserData['id']);

      $points = (count($skladkyData) * 10) + (count($skladkyData) * 3);

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'reported' => count($skladkyData),
          'confirmed' => count($skladkaPotvrdeniaData),
          'cleared' => 0,
          'points' => $points
        ]
      ]);
    break;
    case 'notifikacie': //TODO FEATURE GET 
    break;
    case 'nelegalna-skladka-nahrat-obrazok': //TODO FEATURE POST
      $postData = Request::getPostData();

      Request::validatePostParam('idSkladka');
      Request::validatePostParam('uid');

      if (empty($_FILES)) Response::throwException('$_FILES empty');

      $skladkaModel = $bride->initModel('skladky');
      $skladkaData = $skladkaModel->getById($postData['idSkladka']);

      if (empty($skladkaData)) Response::throwException('Skladka does not exists');

      $filePath = FILES_DIR . '/nelegalne-skladky/' . $skladkaData['id'];

      $uploadSuccess = false;
      foreach ($_FILES as $file) {
        if ($file['size'] > 0) {
          if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
          }

          $fileExtension = explode('.', $file['name']);

          if (!in_array(end($fileExtension), ['jpeg','jpg','png'])) {
            Response::throwException('Allowed format of images: jpeg, jpg, png');
          }

          $imagePath = $filePath . '/' . $file['name'];

          $uploadSuccess = (bool)move_uploaded_file($file['tmp_name'], $imagePath);
        }
      }

      if ($uploadSuccess === true) {
        echo Response::getJson([
          'status' => 'success',
          'message' => 'Images uploaded'
        ]);
      } else {
        Response::throwException('Error with images uploading');
      }
    break;
    case 'test-mail': // TEST PURPOSE
      $mailer = new Mailer();
      var_dump($mailer->sendRegistrationCode("test@xxxx.com", rand(1000, 9999)));
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