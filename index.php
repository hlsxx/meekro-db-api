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
require_once(__DIR__ . '/common.php');

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
  Common::securiter();
  
  $logInfo->info("REQUEST from {$_SERVER['REMOTE_ADDR']}");

  if (!Request::getParam('page')) {
    Response::throwException('GET param: {page} not set');
  }
  
  Common::androidOrIos();

  switch (Request::getParam('page')) {
    case 'skladky-vsetky': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('filter');

      $skladkaFilterModel = new SkladkaModel();
      $skladkaModel = $bride->initModel('skladky');
      $skladkaTypy = $bride->initModel('skladky_typy');
      $skladkaTypyCross = $bride->initModel('skladky_typy_cross');

      $filteredSkladkaData = Request::getParam('pagination') 
        ? $skladkaFilterModel->getPaginationDataFiltered(
            Response::getArray($postData['filter'])
          )
        : $skladkaFilterModel->getAllFiltered(
            Response::getArray($postData['filter'])
          )
      ;

      $skladkaFullData = [];
      foreach ($filteredSkladkaData as $skladkaKey => $skladkaVal) {
        $skladkaFullData[$skladkaKey] = $skladkaVal;

        $skladkaFullData[$skladkaKey]['types'] = $skladkaTypyCross->query("
          SELECT
            {$skladkaTypy->tableName}.id as id,
            {$skladkaTypy->tableName}.nazov as nazov
          FROM {$skladkaTypyCross->tableName}
          LEFT JOIN {$skladkaTypy->tableName} ON {$skladkaTypy->tableName}.id = {model}.id_skladka_typ
          WHERE {model}.id_skladka = %i
        ", (int)$skladkaVal['id']);
      }

      echo Response::getJson([
        'status' => 'success',
        'data' => $skladkaFullData
      ]); 
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
        if (Request::getParamIsset('showType')) {
          $filterSkladkaModel = $bride->initModel('skladky');

          $data = $filterSkladkaModel->query("
            SELECT 
              * 
            FROM {model} 
            WHERE typ = 2
            ORDER BY id DESC
          ");
        } else {
          $data = Request::getParam('pagination') 
            ? $skladkaModel->getPaginationData()
            : $skladkaModel->getAll()
          ;
        }
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
      // TODO: DOROROBIT POD ID_USER

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

      $userModel = $bride->initModel('users');
      $unknownUserModel = $bride->initModel('unknown_users');

      $skladkaDetailData = $skladkaModel->queryFirstRow("
        SELECT 
          {model}.*,
          IFNULL({$userModel->tableName}.name, 'Anonym') as reported_by
        FROM {model}
        LEFT JOIN {$userModel->tableName} 
        ON {$userModel->tableName}.id = {model}.id_user
        LEFT JOIN {$unknownUserModel->tableName} 
        ON {$unknownUserModel->tableName}.id = {model}.id_unknown_user
        WHERE {model}.id = %i
      ", (int)Request::getParam('id'));

      if (empty($skladkaDetailData)) Response::throwException('Skládka neexistuje');

      $skladkaTypyOdpaduData = [];
      if ((int)$skladkaDetailData['typ'] == 2) {
        $skladkaTypyOdpaduData = $skladkaModel->query("
          SELECT
            {$skladkaTypModel->tableName}.id as id,
            {$skladkaTypModel->tableName}.nazov as nazov,
            {$skladkaTypCrossModel->tableName}.pocet_potvrdeni
          FROM {model}
          LEFT JOIN {$skladkaTypCrossModel->tableName} 
          ON {model}.id = {$skladkaTypCrossModel->tableName}.id_skladka
          LEFT JOIN {$skladkaTypModel->tableName} 
          ON {$skladkaTypCrossModel->tableName}.id_skladka_typ = {$skladkaTypModel->tableName}.id
          WHERE {model}.id = " . (int)Request::getParam('id') . "
        ");
      }

      $skladkaGalleryModel = $bride->initModel('skladky_gallery');
      $galleryModel = $bride->initModel('gallery');

      $nelegalneSkladkyImagesFolder = FILES_URL . '/nelegalne-skladky/';

      $skladkaGalleryData = $skladkaGalleryModel->query("
        SELECT
          CONCAT('". $nelegalneSkladkyImagesFolder ."', {model}.id_skladka, '/', link) as link
        FROM {model}
        LEFT JOIN {$galleryModel->tableName} 
        ON {$galleryModel->tableName}.id = {model}.id_gallery
        WHERE {model}.id_skladka = %i
      ", (int)Request::getParam('id'));

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'detail' => $skladkaDetailData,
          'types' => $skladkaTypyOdpaduData,
          'images' => $skladkaGalleryData
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
      //Request::validatePostParam('image');
      Request::validatePostParam('idUser');

      if (Common::getDeviceType() == 2) {
        Request::validatePostParam('size');
      }

      if ($postData['image'] == '') Response::throwException('Pre nahlásenie nelegálnej skládky musíte nahrať obrázok');
      if ($postData['choosenTypes'] == '') Response::throwException('Vyberte aspoň jeden typ odpadu nachádzajúci sa na skládke');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if ((int)$postData['idUser'] != 0)  {
        $userModel = $bride->initModel('users');
        $userData = $userModel->getById($postData['idUser']);

        if (empty($userData)) Response::throwException('Používateľ neexistuje');
      }

      // DEPRECATED 0.36
      /*if ((int)$postData['idUser'] != 0)  {
        $unknownUserData = $unknownUserModel->queryFirstRow("
          SELECT
            *
          FROM {model}
          WHERE uid = %s AND id_user = %i
        ", $postData['uid'], (int)$postData['idUser']);
      } else {
        $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);
      }*/

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

      //$geocodeData = Common::geocoding($postData['lat'], $postData['lng']);

      $httpClient = new \GuzzleHttp\Client();
      $provider = new \Geocoder\Provider\GoogleMaps\GoogleMaps($httpClient, null, Common::reverseThrotle());
      $geocoder = new \Geocoder\StatefulGeocoder($provider, 'sk');

      $result = $geocoder->reverseQuery(\Geocoder\Query\ReverseQuery::fromCoordinates(
        $postData['lat'],
        $postData['lng']
      ))->first();

      if ($result->getCountry()->getCode() != 'SK') Response::throwException('Skládka sa musí nachádzať na území Slovenska');

      $geocodeData = [
        'adresa' => $result->getFormattedAddress(),
        'obec' => $result->getLocality(),
        'okres' => $result->getAdminLevels()->get(2)->getName(),
        'kraj' => $result->getAdminLevels()->get(1)->getName()
      ];

      if (!empty($geocodeData)) {
        $kraj = $geocodeData['kraj'];
        $okres = $geocodeData['okres'];
        $obec = $geocodeData['obec'];
        $sidlo = $geocodeData['adresa'];
      } else {
        $kraj = "{$uniqueId}_kraj";
        $okres = "{$uniqueId}_okres";
        $obec = "{$uniqueId}_obec";
        $sidlo = "{$uniqueId}_sidlo";
      }

      $insertedIdSkladka = $skladkaModel->insert([
        'nazov' => "{$uniqueId}_nazov",
        'kraj' => $kraj,
        'okres' => $okres,
        'obec' => $obec,
        'sidlo' => $sidlo,
        'rok_zacatia' => Date('Y-m-d'),
        'typ' => 2,
        'lat' => (float)$postData['lat'],
        'lng' => (float)$postData['lng'],
        'velkost' => Common::getDeviceType() == 2 
          ? ((float)$postData['size'] != 0 ? (float)$postData['size'] : null)
          : null,
        'id_unknown_user' => (int)$postData['idUser'] != 0 ? null : (int)$unknownUserData['id'],
        'id_user' => (int)$postData['idUser'] != 0 ? (int)$postData['idUser'] : null
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
            Response::throwException('Nastala chyba pri nahrávaní obrázku');
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
                Response::throwException('Nastala chyba pri nahrávaní obrázku');
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
      Request::validatePostParam('idUser');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      if ((int)$postData['idUser'] != 0)  {
        $userModel = $bride->initModel('users');
        $userData = $userModel->getById($postData['idUser']);

        if (empty($userData)) Response::throwException('Používateľ neexistuje');
      }

      // DEPRECATED 0.36
      /*if ((int)$postData['idUser'] != 0)  {
        $unknownUserData = $unknownUserModel->queryFirstRow("
          SELECT
            *
          FROM {model}
          WHERE uid = %s AND id_user = %i
        ", $postData['uid'], (int)$postData['idUser']);
      } else {
        $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);
      }*/

      $idSkladka = (int)$postData['idSkladka'];

      $skladkaModel = new SkladkaModel(); 

      $currentSkladka = $skladkaModel->getById($idSkladka);
      
      $skladkaModel->update([
        'pocet_nahlaseni' => (int)$currentSkladka['pocet_nahlaseni'] + 1
      ], $idSkladka);

      $skladkaPotvrdenieModel = $bride->initModel('skladky_potvrdenia');
      
      $skladkaPotvrdenieModel->insert([
        'id_skladka' => $idSkladka,
        'id_unknown_user' => (int)$postData['idUser'] != 0 ? null : (int)$unknownUserData['id'],
        'id_user' => (int)$postData['idUser'] != 0 ? (int)$postData['idUser'] : null
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
      Request::validatePostParam('idUser');

      if ($postData['image'] == '') Response::throwException('Musíte nahrať obrázok vyčisteného miesta skládky');

      $unknownUserModel = $bride->initModel('unknown_users');
      if ((int)$postData['idUser'] != 0)  {
        $unknownUserData = $unknownUserModel->queryFirstRow("
          SELECT
            *
          FROM {model}
          WHERE uid = %s AND id_user = %i
        ", $postData['uid'], (int)$postData['idUser']);
      } else {
        $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);
      }

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $idSkladka = (int)$postData['idSkladka'];

      $skladkaModel = $bride->initModel('skladky');

      $currentSkladka = $skladkaModel->getById($idSkladka);

      if (empty($currentSkladka)) Response::throwException('Skládka nebola rozpoznaná v systéme');
      
      $skladkaModel->update([
        'vycistena' => 1
      ], $idSkladka);

      $skladkaVycisteneModel = $bride->initModel('skladky_vycistene');
      
      $idSkladkaVycistena = $skladkaVycisteneModel->insert([
        'id_skladka' => $idSkladka,
        'id_unknown_user' => (int)$unknownUserData['id'],
        'okres' => $currentSkladka['okres'],
        'kraj' => $currentSkladka['kraj'],
        'obec' => $currentSkladka['obec'],
        'sidlo' => $currentSkladka['sidlo'],
        'rok_zacatia' => $currentSkladka['rok_zacatia'],
        'lat' => $currentSkladka['lat'],
        'lng' => $currentSkladka['lng'],
        'velkost' => $currentSkladka['velkost'],
        'id_unknown_user_cleared' => (int)$unknownUserData['id'],
        'id_unknown_user_reported' => (int)$currentSkladka['idUser'] != 0 ? null : (int)$currentSkladka['id_unknown_user'],
        'id_user_reported' => (int)$currentSkladka['idUser'] != 0 ? (int)$currentSkladka['idUser'] : null,
        'created_at' => date('Y-m-d H:i:s')
      ]);

      $filePath = FILES_DIR . '/nelegalne-skladky/' . $idSkladka;

      $galleryModel = $bride->initModel('gallery');
      $skladkyVycisteneGalleryModel = $bride->initModel('skladky_vycistene_gallery');

      if (!file_exists($filePath)) {
        if (!mkdir($filePath, 0755, true)) {
          Response::throwException('Nastala chyba pri nahrávaní obrázku');
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
      $newName = ($countExistingImages + 1) . '_cleared' . $ext;

      $imagePath = $filePath . '/' . $newName;
    
      if (!file_put_contents($imagePath, $image)) {
        Response::throwException('Nastala chyba pri nahrávaní obrázku');
      }

      $idGallery = $galleryModel->insert([
        'link' => $newName,
        'created_at' => date('Y-m-d H:i:s')
      ]);

      $skladkyVycisteneGalleryModel->insert([
        'id_skladka_vycistena' => (int)$idSkladkaVycistena,
        'id_gallery' => (int)$idGallery,
        'id_unknown_user' => (int)$unknownUserData['id']
      ]);

      echo Response::getJson([
        'status' => 'success'
      ]);  
    break;
    case 'potvrdil-som': // GET
      $getData = Request::getGetData();

      Request::validateGetParam('idSkladka');
      Request::validateGetParam('uid');
      Request::validateGetParam('idUser');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $getData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $skladkaModel = $bride->initModel('skladky');

      if ((int)$getData['idUser'] != 0)  {
        $userModel = $bride->initModel('users');
        $userData = $userModel->getById($getData['idUser']);

        if (empty($userData)) Response::throwException('Používateľ neexistuje');

        $skladkaReportedByUserData = $skladkaModel->query("
          SELECT
            *
          FROM {model}
          WHERE id = %i AND id_user = %i
        ", (int)$getData['idSkladka'], (int)$userData['id']);

        if (empty($skladkaReportedByUserData)) {
          $skladkaPotvrdenieModel = new SkladkaPotvrdenieModel();
  
          $skladkaConfirmedByUserData = [];
          $skladkaConfirmedByUserData = DB::queryFirstRow("
            SELECT
              *
            FROM {$skladkaPotvrdenieModel->tableName}
            WHERE id_skladka = %i AND id_user = %i
          ", (int)$getData['idSkladka'], (int)$userData['id']);
        }
      } else {
        $skladkaReportedByUserData = $skladkaModel->query("
          SELECT
            *
          FROM {model}
          WHERE id = %i AND id_unknown_user = %i
        ", (int)$getData['idSkladka'], (int)$unknownUserData['id']);
  
        $skladkaConfirmedByUserData = [];
        if (empty($skladkaReportedByUserData)) {
          $skladkaPotvrdenieModel = new SkladkaPotvrdenieModel();
  
          $skladkaConfirmedByUserData = DB::queryFirstRow("
            SELECT
              *
            FROM {$skladkaPotvrdenieModel->tableName}
            WHERE id_skladka = %i AND id_unknown_user = %i
          ", (int)$getData['idSkladka'], (int)$unknownUserData['id']);
        }
      }

      //DEPRECATED 0.36
      /*if ((int)$getData['idUser'] != 0)  {
        $unknownUserData = $unknownUserModel->queryFirstRow("
          SELECT
            *
          FROM {model}
          WHERE uid = %s AND id_user = %i
        ", $getData['uid'], (int)$getData['idUser']);
      } else {
        $unknownUserData = $unknownUserModel->getByCustom('uid', $getData['uid']);
      }*/

      echo Response::getJson([
        'status' => 'success',
        'reportedByUser' => !empty($skladkaReportedByUserData),
        'confirmedByUser' => !empty($skladkaConfirmedByUserData)
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
      Request::validatePostParam('idUser');
      Request::validatePostParam('password');

      $unknownUserModel = $bride->initModel('unknown_users');
      if ((int)$postData['idUser'] != 0)  {
        $unknownUserData = $unknownUserModel->queryFirstRow("
          SELECT
            *
          FROM {model}
          WHERE uid = %s AND id_user = %i
        ", $postData['uid'], (int)$postData['idUser']);
      } else {
        $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);
      }

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      $unknownUserModel->update(
        [
          'last_login' => date('Y-m-d H:i:s', time())
        ],
        (int)$unknownUserData['id']
      );

      // Logged user
      $userLoggedData = [];
      if ((int)$postData['idUser'] > 0) {
        $userModel = $bride->initModel('users');
        $userData = $userModel->getById($postData['idUser']);
        if (empty($userData)) Response::throwWarning('Nepodarilo sa prihlásiť');

        if ($postData['password'] != $userData['password']) {
          Response::throwWarning('Nepodarilo sa prihlásiť');
        }

        $userModel->update(
          [
            'last_login' => date('Y-m-d H:i:s', time())
          ],
          (int)$userData['id']
        );

        $userLoggedData = [
          'email' => Helper::deleteSpaces($userData['email']),
          'idUser' => $userData['id'],
          'name' => $userData['name'],
          'password' => $userData['password']
        ];
      }

      echo Response::getJson([
        'status' => 'success',
        'data' => $userLoggedData
      ]); 
    break;
    case 'registracia': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('email');
      Request::validatePostParam('password_1');
      Request::validatePostParam('password_2');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustom('uid', $postData['uid']);

      if (empty($unknownUserData)) Response::throwException('Vaše zariadenie nebolo rozpoznané v systéme');

      if ($postData['email'] == '') Response::throwException('Nezadali ste e-mailovú adresu');
      if ($postData['password_1'] == '') Response::throwException('Nezadali heslo');
      if ($postData['password_2'] == '') Response::throwException('Nezadali ste overovacie heslo');

      if (!filter_var(Helper::deleteSpaces($postData['email']), FILTER_VALIDATE_EMAIL)) {
        Response::throwException('Nesprávny formát e-mailu');
      }

      if (strlen($postData['password_1']) < 8) {
        Response::throwException('Heslo musí obsahovať aspoň 8 znakov');
      }

      if ($postData['password_1'] != $postData['password_2']) {
        Response::throwException('Hesla sa nezhodujú');
      }

      $userModel = $bride->initModel('users');
      $userAlreadyExists = $userModel->getByCustom('email', Helper::deleteSpaces($postData['email']));

      if (!empty($userAlreadyExists)) Response::throwException('Tento e-mail je už použitý');

      $createdAt = date('Y-m-d H:i:s');
      $idUser = $userModel->insert([
        'email' => Helper::deleteSpaces($postData['email']),
        'password' => password_hash($postData['password_1'], PASSWORD_BCRYPT),
        'created_at' => $createdAt
      ]);

      $insertedUnknownUserId = $unknownUserModel->insert([
        'uid' => $unknownUserData['uid'],
        'created_at' => $createdAt
      ]);

      // TODO: PREROBIT
      /*
        $unknownUserUserCrossModel = $bride->initModel('unknown_users_users_cross');
        $unknownUserUserCrossModel->insert([
          'id_unknown_user' => (int)$unknownUserData['id'],
          'id_user' => (int)$idUser
        ]);
      */

      $tokenNumber = (new TokenModel())->getTokenNumber();

      $tokenModel = $bride->initModel('tokens');
      $tokenModel->insert([
        'id_user' => (int)$idUser,
        'id_unknown_user' => (int)$insertedUnknownUserId,
        'attempt' => 3,
        'type' => TokenModel::$types['registration'],
        'token_number' => $tokenNumber,
        'created_at' => $createdAt
      ]);

      if (DISABLE_MAIL == FALSE) {
        if (strpos(Helper::deleteSpaces($postData['email']), 'testx') === false) {
          $mailer = new Mailer();
          $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
        }
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

      if ($postData['email'] == '') Response::throwException('Nezadali ste e-mailovú adresu');
      if ($postData['password'] == '') Response::throwException('Nezadali heslo');

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

      // Ak sa prihlasim z ineho zariadenia pridam ho
      // TODO: PREROBIT
      /*$unknownUserUserCrossModel = $bride->initModel('unknown_users_users_cross');
      $unknownUserUserCrossData = $unknownUserUserCrossModel->queryFirstRow("
        SELECT
          *
        FROM {model}
        WHERE id_unknown_user = %i AND id_user = %i
      ", (int)$unknownUserData, (int)$userData['id']);

      if (empty($unknownUserUserCrossData)) {
        $unknownUserUserCrossModel->insert([
          'id_unknown_user' => (int)$unknownUserData['id'],
          'id_user' => (int)$userData['id']
        ]);
      }*/

      // DEPRECATED
      if ((int)$unknownUserData['id_user'] != (int)$userData['id']) {
        $unknownUserModel->insert([
          'uid' =>  $postData['uid'],
          'id_user' => (int)$userData['id'],
          'created_at' => date('Y-m-d H:i:s')
        ]);
      }

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'email' => Helper::deleteSpaces($userData['email']),
          'idUser' => $userData['id'],
          'name' => (string)$userData['name'],
          'password' => $userData['password']
        ]
      ]);
    break;
    case 'registracia-validacia': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('token_number');
      Request::validatePostParam('uid');

      $unknownUserModel = $bride->initModel('unknown_users');
      $unknownUserData = $unknownUserModel->getByCustomLast('uid', $postData['uid']);

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

      $userModel = $bride->initModel('users');
      $userData = $userModel->getById((int)$tokenData['id_user']);

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

        if (empty($userData)) Response::throwException('Nastala chyba, uživateľ nebol rozpoznaný');

        if (DISABLE_MAIL == FALSE) {
          if (!strpos($userData['email'], 'testx') == false) {
            $mailer = new Mailer();
            $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
          }
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
          'message' => 'Successful verified',
          'data' => [
            'email' => Helper::deleteSpaces($userData['email']),
            'idUser' => $userData['id'],
            'name' => '',
            'password' => $userData['password']
          ]
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
      Request::validateGetParam('idUser');

      $userModel = $bride->initModel('users');
      $userData = $userModel->getById($getData['idUser']);
      if (empty($userData)) Response::throwWarning('Účet nebol rozpoznaný');

      // DEPRECATED 0.36
      /*$unknownUserModel = $bride->initModel('unknown_users');

      $unknownUserData = $unknownUserModel->query("
        SELECT
          id
        FROM {model}
        WHERE id_user = %i
      ", (int)$userData['id']);

      $unknowUsersIds = [];
      foreach ($unknownUserData as $item) {
        $unknowUsersIds[] = $item['id'];
      }*/

      /*$unknownUserData = $unknownUserModel->queryFirstRow("
        SELECT
          *
        FROM {model}
        WHERE uid = %s AND id_user = %i
      ", $getData['uid'], (int)$userData['id']);*/

      $skladkaModel = $bride->initModel('skladky');

      $skladkyData = $skladkaModel->query("
        SELECT 
          *
        FROM {model}
        WHERE id_user = %i 
      ", (int)$userData['id']);

      $skladkaPotvrdeniaModel = $bride->initModel('skladky_potvrdenia');
      $skladkaPotvrdeniaData = $skladkaPotvrdeniaModel->query("
        SELECT 
          *
        FROM {model}
        WHERE id_user = %i 
      ", (int)$userData['id']);

      $skladkyDataCount = count((array)$skladkyData);
      $confirmedCount = count((array)$skladkaPotvrdeniaData);

      $percentageTogether = ($skladkyDataCount + $confirmedCount) != 0 ?
        100 / ($skladkyDataCount + $confirmedCount)
        : 0 
      ;

      $skladkyDataPercentage = $percentageTogether * $skladkyDataCount;
      $confirmedPercentage = $percentageTogether * $confirmedCount;

      $points = ($skladkyDataCount * 10) + ($confirmedCount * 3);

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'reported' => $skladkyDataCount,
          'reportedPercentage' =>  $skladkyDataPercentage,
          'confirmed' => $confirmedCount,
          'confirmedPercentage' => $confirmedPercentage,
          'cleared' => 0,
          'points' => (int)$points,
          'rank' => 5,
          'name' => $userData['name'] ?: 'Anonym'
        ]
      ]);
    break;
    case 'ucet-vymazat':
      $postData = Request::getPostData();

      Request::validatePostParam('idUser');

      $userModel = $bride->initModel('users');

      $userData = $userModel->getById((int)$postData['idUser']);

      if (empty($userData)) Response::throwException('Nastala chyba, uživateľ nebol rozpoznaný');

      $userModel->delete((int)$userData['id']);

      echo Response::getJson([
        'status' => 'success',
        'message' => 'Ucet úspešne odstránený'
      ]);
    break;
    case 'prehlad': // GET
      $userModel = $bride->initModel('users');
      $unknownUserModel = $bride->initModel('unknown_users');
      $skladkaModel = $bride->initModel('skladky');
      $skladkaPotvrdenieModel = $bride->initModel('skladky_potvrdenia');

      /*$nelegalneSkladkyData = $skladkaModel->query("
        SELECT
          IFNULL({$userModel->tableName}.name, '') as name,
          (
            IFNULL(nested.confirmedPoints, 0) 
              + 
            IFNULL(nested.reportedPoints, 0)
          ) as total
        FROM (
          SELECT
            id_unknown_user, 
            (
              SELECT
                (COUNT(*) * 3) as count
              FROM {$skladkaPotvrdenieModel->tableName}
              WHERE {$skladkaPotvrdenieModel->tableName}.id_unknown_user = {model}.id_unknown_user
              GROUP BY {$skladkaPotvrdenieModel->tableName}.id_unknown_user
              ORDER BY count DESC
            ) as confirmedPoints,
            (COUNT(*) * 10) as reportedPoints
          FROM {model}
          WHERE typ = 2
          GROUP BY {model}.id_unknown_user
          ORDER BY reportedPoints DESC
        ) as nested
        LEFT JOIN {$unknownUserModel->tableName} 
        ON {$unknownUserModel->tableName}.id = nested.id_unknown_user
        LEFT JOIN {$userModel->tableName} 
        ON {$userModel->tableName}.id = {$unknownUserModel->tableName}.id_user
        ORDER BY total DESC
        LIMIT 5
      ");*/

      $nelegalneSkladkyData = $skladkaModel->query("
        SELECT
          IFNULL({$userModel->tableName}.name, '') as name,
          (
            IFNULL(nested.confirmedPoints, 0) 
              + 
            IFNULL(nested.reportedPoints, 0)
          ) as total
        FROM (
          SELECT
            id_user,
            (
              SELECT
                (COUNT(*) * 3) as count
              FROM {$skladkaPotvrdenieModel->tableName}
              WHERE {$skladkaPotvrdenieModel->tableName}.id_user = {model}.id_user
              GROUP BY {$skladkaPotvrdenieModel->tableName}.id_user
              ORDER BY count DESC
            ) as confirmedPoints,
            (COUNT(*) * 10) as reportedPoints
          FROM {model}
          WHERE typ = 2 AND id_user IS NOT NULL
          GROUP BY {model}.id_user
          ORDER BY reportedPoints DESC
        ) as nested
        LEFT JOIN {$userModel->tableName} ON {$userModel->tableName}.id = nested.id_user
        ORDER BY total DESC
        LIMIT 5
      ");

      $allIllegal = count($skladkaModel->query("
        SELECT
          *
        FROM {model}
        WHERE typ = 2
      "));

      $allLegal = count($skladkaModel->query("
        SELECT
          *
        FROM {model}
        WHERE typ = 1
      "));

      echo Response::getJson([
        'status' => 'success',
        'data' => [
          'topUsers' => $nelegalneSkladkyData,
          'illegalCount' => $allIllegal,
          'legalCount' => $allLegal,
          'clearedCount' => 0
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
    case 'zmena-mena': // POST
      $postData = Request::getPostData();

      Request::validatePostParam('id_user');
      Request::validatePostParam('name');

      if ((strlen($postData['name']) < 2)) Response::throwException('Meno musí obsahovať aspoň 2 znaky');

      $userModel = $bride->initModel('users');

      $allUsersData = $userModel->getAll();

      if (strlen($postData['name']) > 30) Response::throwException('Povolená dĺžka mena je 30 znakov.');

      foreach ($allUsersData as $data) {
        if ($data['name'] == $postData['name']) Response::throwException('Toto meno je už obsadené, skúste iné.');
      }

      $userData = $userModel->getById((int)$postData['id_user']);

      if (empty($userData)) Response::throwException('Neznámy užívateľ');

      $userModel->update([
        'name' => $postData['name']
      ], (int)$postData['id_user']);

      echo Response::getJson([
        'status' => 'success',
        'message' => 'Meno úspešne zmenené'
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
        'message' => 'Heslo úspešne zmenené',
        'data' => [
          'email' => Helper::deleteSpaces($userData['email']),
          'idUser' => $userData['id'],
          'name' => $userData['name'],
          'password' => $postData['new_password']
        ]
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

      if (DISABLE_MAIL == FALSE) {
        if (!strpos(Helper::deleteSpaces($postData['email']), 'testx') == false) {
          $mailer = new Mailer();
          $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
        }
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

        if (DISABLE_MAIL == FALSE) {
          if (!strpos($userData['email'], 'testx') == false) {
            $mailer = new Mailer();
            $mailer->sendRegistrationCode(Helper::deleteSpaces($postData['email']), $tokenNumber);
          }
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
    case 'texty': // GET
      $getData = Request::getGetData();

      Request::validateGetParam('textPage');
      Request::validateGetParam('device_type');

      $appTextModel = $bride->initModel('app_texts');
  
      $textsData = $appTextModel->query("
        SELECT
          *
        FROM {model}
        WHERE page = %s AND device_type = %i
      ", $getData['textPage'], (int)$getData['device_type']);

      $returnTextsData = [];
      foreach ($textsData as $textData) {
        $returnTextsData[$textData['type']][] = [
          'id' => $textData['id'],
          'text' => $textData['text']
        ];
      }

      echo Response::getJson([
        'status' => 'success',
        'data' => $returnTextsData
      ]);
    break;
    case 'dev-tokens':
      $tokenModel = $bride->initModel('tokens');
      foreach($tokenModel->getAll() as $token) {
        echo "IDUSER: <b>{$token['id_user']}</b> IDUID: <b>{$token['id_unknown_user']}</b> TOKEN: <b>{$token['token_number']}</b> VYTVORENE:{$token['created_at']} <br>";
      }
    break;
    case 'cron-mesiac':
      // DELETE ALL TOKENS
      $tokenModel = $bride->initModel('tokens');
      $tokenModel->query("TRUNCATE {model}");
    break;
    case 'insert-idea':
      $postData = Request::getPostData();

      Request::validatePostParam('device_type');
      Request::validatePostParam('type');
      Request::validatePostParam('text');

      $ideaModel = $bride->initModel('ideas');
      $ideaModel->insert([
        'email' => $postData['email'],
        'device_type' => (int)$postData['device_type'],
        'type' => (int)$postData['type'],
        'text' => $postData['text']
      ]);

      echo Response::getJson([
        'status' => 'success'
      ]);
    break;
    default:
      Response::throwException('PAGE: {' . Request::getParam('page') . '} doesnt exists');
    break;
  }
} catch(\Exception $e) {
  $requestParams = isset($postData) ? $postData : (isset($getData) ? $getData : []);
  if (empty($requestParams['image'])) $requestParams['image'] = '';
  
  $logError->error($e->getMessage() . json_encode($requestParams));
  
  echo Response::getErrorJson($e);
}

?>