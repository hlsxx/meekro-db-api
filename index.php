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

require_once(__DIR__ . '/common.php');

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
  
  Common::androidOrIos();

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

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');
      if ($unknownUserData['id_user'] == NULL) Response::throwException('Váš uživateľský účet je neplatný');

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

      $skladkaModel = $bride->initModel('skladky');
      $skladkaTypCrossModel = $bride->initModel('skladky_typy_cross');
      $skladkaTypModel = $bride->initModel('skladky_typy');

      $skladkaTypyOdpaduData = $skladkaModel->query("
        SELECT
          {$skladkaTypModel->tableName}.nazov as nazov,
          {$skladkaTypCrossModel->tableName}.pocet_potvrdeni
        FROM {model}
        LEFT JOIN {$skladkaTypCrossModel->tableName} 
        ON {model}.id = {$skladkaTypCrossModel->tableName}.id_skladka
        LEFT JOIN {$skladkaTypModel->tableName} 
        ON {$skladkaTypCrossModel->tableName}.id_skladka_typ = {$skladkaTypModel->tableName}.id
        WHERE {model}.id = " . (int)Request::getParam('id') . "
      ");

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'detail' => $skladkaModel->getById((int)Request::getParam('id')),
          'types' => $skladkaTypyOdpaduData
        ]
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
      Request::validatePostParam('image');

      if ($postData['image'] == '') Response::throwException('Pre nahlásenie nelegálnej skládky musíte nahrať obrázok');
      if ($postData['choosenTypes'] == '') Response::throwException('Vyberte aspoň jeden typ odpadu nachádzajúci sa na skládke');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $skladkaModel = $bride->initModel('skladky');

      $uniqueId = uniqid();

      $disableNear = (isset($postData['disableNear']) && (bool)$postData['disableNear'] == true);

      if (!$disableNear) {
        $reserDistance = 0.0001;
        $reportedNearData = $skladkaModel->query("
          SELECT 
            *
          FROM {model}
          WHERE lat > " . ((float)$postData['lat'] - $reserDistance) . " AND lat < " . ((float)$postData['lat'] + $reserDistance)
          . "OR lng > " . ((float)$postData['lng'] - $reserDistance) . " AND lng < " . ((float)$postData['lng'] + $reserDistance)
        );
      }

      if (!empty($reportedNearData)) {
        echo Response::getJson([
          'status' => 'warning',
          'message' => 'V blískosti sa nachádza nahlásená skládka. Nejedná sa o skládku ktorú chcete nahlásiť?',
          'data' => [
            'nearCount' => count($reportedNearData),
            'data' => $reportedNearData
          ]
        ]); 

        exit();
      }

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
      if (isset($postData['image'])) {
        $filePath = FILES_DIR . '/nelegalne-skladky/' . $insertedIdSkladka;

        $galleryModel = $bride->initModel('gallery');
        $skladkyGalleryModel = $bride->initModel('skladky_gallery');

        if (!file_exists($filePath)) {
          if (!mkdir($filePath, 0755, true)) {
            Response::throwException('Error with creating folder');
          }
        }
        
        if (strpos($postData['image'], 'data:image') !== false) {
          $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $postData['image']));
          $ext = '.jpg';
        } else {
          // IOS
          $image = str_replace(" ", "+", $postData['image']);
          $image = base64_decode($image);
          $ext = '.jpeg';
        }

        $countExistingImages = count(scandir($filePath)) - 2;
        $newName = ($countExistingImages + 1) . $ext;

        $imagePath = $filePath . '/' . $newName;
      
        if (!file_put_contents($imagePath, $image)) {
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

        // $_FILES deprecated
        /*foreach ($_FILES as $file) {
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
        }*/
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

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

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
      Request::validatePostParam('image');

      if ($postData['image'] == '') Response::throwException('Musíte nahrať obrázok vyčisteného miesta skládky');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $idSkladka = (int)$postData['idSkladka'];

      $skladkaModel = $bride->initModel('skladky');

      $currentSkladka = $skladkaModel->getById($idSkladka);
      
      $skladkaModel->update([
        'vycistena' => 1
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

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $skladkaPotvrdenieModel = new SkladkaPotvrdenieModel();

      $data = DB::queryFirstRow("
        SELECT
          *
        FROM {$skladkaPotvrdenieModel->tableName}
        WHERE id_skladka = %i AND id_unknown_user = %i
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

      if ($insertedUnknownUserId === false) Response::throwException('Nastala neočakávana chyba');

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

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

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
      Request::validatePostParam('password_1');
      Request::validatePostParam('password_2');
      Request::validatePostParam('uid');

      if (!filter_var(Helper::deleteSpaces($postData['email']), FILTER_VALIDATE_EMAIL)) {
        Response::throwException('Nesprávny formát e-mailu');
      }

      if (strlen($postData['password_1']) < 8) {
        Response::throwException('Heslo musí obsahovať aspoň 8 znakov');
      }

      if ($postData['password_1'] != $postData['password_2']) {
        Response::throwException('Hesla sa nezhodujú');
      }

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $userModel = $bride->initModel('users');
      $userAlreadyExists = $userModel->getByCustom('email', Helper::deleteSpaces($postData['email']));

      if (!empty($userAlreadyExists)) Response::throwException('Tento e-mail je už použitý');

      $createdAt = date('Y-m-d H:i:s');
      $idUser = $userModel->insert([
        'email' => Helper::deleteSpaces($postData['email']),
        'password' => password_hash($postData['password_1'], PASSWORD_BCRYPT),
        'created_at' => $createdAt
      ]);

      $tokenNumber = (new TokenModel())->getTokenNumber();

      $tokenModel = $bride->initModel('tokens');
      $tokenModel->insert([
        'id_user' => (int)$idUser,
        'id_unknown_user' => (int)$unknownUserData['id'],
        'attempt' => 3,
        'type' => TokenModel::$types['registration'],
        'token_number' => $tokenNumber,
        'created_at' => $createdAt
      ]);

      if (!strpos(Helper::deleteSpaces($postData['email']), 'testx') == false) {
        $mailer = new Mailer();
        $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
      }
      
      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'email' => Helper::deleteSpaces($postData['email']),
          'id_user' => $idUser,
          'name' => ''
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

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      if (!filter_var(Helper::deleteSpaces($postData['email']), FILTER_VALIDATE_EMAIL)) {
        Response::throwException('Nesprávny formát e-mailu');
      }

      $userModel = $bride->initModel('users');
      $userData = $userModel->getByCustom('email', Helper::deleteSpaces($postData['email']));

      if (empty($userData)) Response::throwException('Zadaná e-mailová adresa neexistuje');

      if (!password_verify($postData['password'], $userData['password'])) {
        Response::throwException('Heslo je nesprávne');
      }

      if ((bool)$userData['verified'] == false) Response::throwException('Váš účet nie je overený');

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'email' => Helper::deleteSpaces($userData['email']),
          'id_user' => $userData['id'],
          'name' => $userData['name']
        ]
      ]);
    break;
    case 'registracia-validacia': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('token_number');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $tokenModel = $bride->initModel('tokens');

      $tokenData = DB::queryFirstRow("
        SELECT 
          *
        FROM {$tokenModel->tableName} 
        WHERE id_unknown_user = %i
        AND type = " . TokenModel::$types['registration'] . "
        ORDER BY id DESC
      ", (int)$unknownUserData['id']);

      if (empty($tokenData)) Response::throwException('Token neexistuje pre vaše zariadenie: ' . $postData['uid']);
      
      $timeToTokenValid = strtotime($tokenData['created_at'] . ' + 10 minute');

      if (date('Y-m-d H:i:s', $timeToTokenValid) < date('Y-m-d H:i:s')) {
        $tokenModel->delete($tokenData['id']);

        $tokenNumber = (new TokenModel())->getTokenNumber();

        $tokenModel = $bride->initModel('tokens');
        $tokenModel->insert([
          'id_user' => (int)$tokenData['id_user'],
          'id_unknown_user' => (int)$unknownUserData['id'],
          'attempt' => 3,
          'type' => TokenModel::$types['registration'],
          'token_number' => $tokenNumber,
          'created_at' => date('Y-m-d H:i:s')
        ]);

        $userModel = $bride->initModel('users');
        $userData = $userModel->getById((int)$tokenData['id_user']);

        if (empty($userData)) Response::throwException('Nastala chyba, uživateľ nebol rozpoznaný');

        if (!strpos($userData['email'], 'testx') == false) {
          $mailer = new Mailer();
          $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
        }

        Response::throwException('Token už expiroval. Zaslali sme Vám nový.');
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

        Response::throwExceptionWithData(
          'Zadaný kód je nesprávny, skúste to znovu',
          [
            'remainingsAttempts' => (int)$tokenData['attempt'] - 1
          ]
        );
      }
    break;
    case 'ucet-prehlad': // GET
      $getData = Request::getGetData();

      Request::validateGetParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $getData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

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

      $skladkyDataCount = count((array)$skladkyData);
      $confirmedCount = count((array)$skladkaPotvrdeniaData);

      $percentageTogether = 100 / ($skladkyDataCount + $confirmedCount);

      $skladkyDataPercentage = $percentageTogether * $skladkyDataCount;
      $confirmedPercentage = $percentageTogether * $confirmedCount;

      $points = (count($skladkyData) * 10) + (count($skladkyData) * 3);

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'reported' => $skladkyDataCount,
          'reportedPercentage' =>  $skladkyDataPercentage,
          'confirmed' => $confirmedCount,
          'confirmedPercentage' => $confirmedPercentage,
          'cleared' => 0,
          'points' => (int)$points
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

      if (empty($skladkaData)) Response::throwException('Skládka neexistuje');

      $filePath = FILES_DIR . '/nelegalne-skladky/' . $skladkaData['id'];

      $uploadSuccess = false;
      foreach ($_FILES as $file) {
        if ($file['size'] > 0) {
          if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
          }

          $fileExtension = explode('.', $file['name']);

          if (!in_array(end($fileExtension), ['jpeg','jpg','png'])) {
            Response::throwException('Povolené formáty: jpeg, jpg, png');
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
    case 'test-image': // TEST PURPOSE
      $postData = Request::getPostData();

      $data = str_replace(" ", "+", $postData['image']);
      $data = base64_decode($data);

      file_put_contents('test_peter/image.jpeg', $data);
    break;
    case 'test-mail':
      $mailer = new Mailer();
      $mailer->sendRegistrationCode("peter.hafner9@gmail.com", 1111);
    break;
    case 'zmena-mena': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('id_user');
      Request::validatePostParam('name');

      if ((strlen($postData['name']) < 2)) Response::throwException('Prezývka musí obsahovať aspoň 2 znaky');

      $userModel = $bride->initModel('users');

      $allUsersData = $userModel->getAll();

      foreach ($allUsersData as $data) {
        if ($data['name'] == $postData['name']) Response::throwException('Táto prezývka je už obsadená');
      }

      $userData = $userModel->getById((int)$postData['id_user']);

      if (empty($userData)) Response::throwException('Neznámy užívateľ');

      $userModel->update([
        'name' => $postData['name']
      ], (int)$postData['id_user']);

      echo Response::getJson([
        'status' => 'success',
        'message' => 'Prezývka úspešne zmenená'
      ]);
    break;
    case 'zmena-hesla': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('id_user');
      Request::validatePostParam('old_password');
      Request::validatePostParam('new_password');

      $userModel = $bride->initModel('users');
      $userData = $userModel->getById((int)$postData['id_user']);

      if (empty($userData)) Response::throwException('Unknown user');

      if (!password_verify($postData['old_password'], $userData['password'])){
        Response::throwException('Vaše súčasné heslo nie je správne');
      }

      if (strlen($postData['new_password']) < 8) {
        Response::throwException('Nové heslo musí obsahovať aspoň 8 znakov');
      }

      $userModel->update([
        'password' => password_hash($postData['new_password'], PASSWORD_BCRYPT)
      ], (int)$postData['id_user']);

      echo Response::getJson([
        'status' => 'success',
        'message' => 'Heslo úspešne zmenené'
      ]);
    break;
    case 'zabudnute-heslo': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('email');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      if (!filter_var(Helper::deleteSpaces($postData['email']), FILTER_VALIDATE_EMAIL)) {
        Response::throwException('Nesprávny formát e-mailu');
      }

      $userModel = $bride->initModel('users');
      $userData = $userModel->getByCustom('email', Helper::deleteSpaces($postData['email']));

      if (empty($userData)) Response::throwException('Zadaná e-mailová adresa neexistuje');

      $tokenNumber = (new TokenModel())->getTokenNumber();

      $tokenModel = $bride->initModel('tokens');
      $tokenModel->insert([
        'id_user' => (int)$userData['id'],
        'id_unknown_user' => (int)$unknownUserData['id'],
        'attempt' => 3,
        'type' => TokenModel::$types['forgotten_password'],
        'token_number' => $tokenNumber,
        'created_at' => date('Y-m-d H:i:s')
      ]);

      if (!strpos(Helper::deleteSpaces($postData['email']), 'testx') == false) {
        $mailer = new Mailer();
        $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
      }

      echo Response::getJson([
        'status' => 'success',
        'message' => 'Na Váš e-mail bol zaslaný overovací kód',
        'data' => [
          'idUser' => $userData['id']
        ]
      ]);
    break;
    case 'zabudnute-heslo-validacia': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('token_number');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $tokenModel = $bride->initModel('tokens');

      $tokenData = DB::queryFirstRow("
        SELECT 
          *
        FROM {$tokenModel->tableName} 
        WHERE id_unknown_user = %i
        AND type = " . TokenModel::$types['forgotten_password'] . "
        ORDER BY id DESC
      ", (int)$unknownUserData['id']);

      if (empty($tokenData)) Response::throwException('Token neexistuje pre vaše zariadenie: ' . $postData['uid']);
      
      $timeToTokenValid = strtotime($tokenData['created_at'] . ' + 10 minute');

      if (date('Y-m-d H:i:s', $timeToTokenValid) < date('Y-m-d H:i:s')) {
        $tokenModel->delete($tokenData['id']);

        $tokenNumber = (new TokenModel())->getTokenNumber();

        $tokenModel = $bride->initModel('tokens');
        $tokenModel->insert([
          'id_user' => (int)$tokenData['id_user'],
          'id_unknown_user' => (int)$unknownUserData['id'],
          'attempt' => 3,
          'type' => TokenModel::$types['forgotten_password'],
          'token_number' => $tokenNumber,
          'created_at' => date('Y-m-d H:i:s')
        ]);

        $userModel = $bride->initModel('users');
        $userData = $userModel->getById((int)$tokenData['id_user']);

        if (empty($userData)) Response::throwException('Nastala chyba, uživateľ nebol rozpoznaný');

        if (!strpos($userData['email'], 'testx') == false) {
          $mailer = new Mailer();
          $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
        }

        Response::throwException('Token už expiroval. Zaslali sme Vám nový.');
      }

      if ((int)$tokenData['token_number'] == (int)$postData['token_number']) {
        $tokenModel->delete($tokenData['id']);

        echo Response::getJson([
          'status' => 'success'
        ]);
      } else {
        $tokenModel->update([
          'attempt' => (int)$tokenData['attempt'] - 1
        ], $tokenData['id']);

        Response::throwExceptionWithData(
          'Zadaný kód je nesprávny, skúste to znovu',
          [
            'remainingsAttempts' => (int)$tokenData['attempt'] - 1
          ]
        );
      }
    break;
    case 'zabudnute-heslo-nove-heslo': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('idUser');
      Request::validatePostParam('newPassword');
      Request::validatePostParam('newPassword2');

      $userModel = $bride->initModel('users');
      $userData = $userModel->getById((int)$postData['idUser']);

      if (empty($userData)) Response::throwException('Unknown user');

      if (strlen($postData['newPassword']) < 8) {
        Response::throwException('Nové heslo musí obsahovať aspoň 8 znakov');
      }

      if ($postData['newPassword'] != $postData['newPassword2']) {
        Response::throwException('Hesla sa nezhodujú');
      }

      $userModel->update([
        'password' => password_hash($postData['newPassword'], PASSWORD_BCRYPT)
      ], (int)$postData['idUser']);

      echo Response::getJson([
        'status' => 'success',
        'message' => 'Heslo úspešne nastavené'
      ]);
    break;
    case 'dev-tokens':
      $tokenModel = $bride->initModel('tokens');
      foreach($tokenModel->getAll() as $token) {
        echo "IDUSER: <b>{$token['id_user']}</b> IDUID: <b>{$token['id_unknown_user']}</b> TOKEN: <b>{$token['token_number']}</b> VYTVORENE:{$token['created_at']} <br>";
      }
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