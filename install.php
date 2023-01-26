<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/app.php');

require_once('./config.php');
require_once(__DIR__ . '/vendor/autoload.php');

$bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);

$bride->tablePrefix('ucm');

echo "<small>Meekro-api version: " . APP_VERSION . '</small></br></br>';
/** UCM_SKLADKY_VYCISTENE */
$skladkaVycisteneModel = $bride->initModel('skladky_vycistene');

$skladkaVycisteneModel->defineColumn('id_skladka')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('okres')->type('varchar')->size(60)->null(false);
$skladkaVycisteneModel->defineColumn('kraj')->type('varchar')->size(60)->null(false);
//$skladkaVycisteneModel->defineColumn('nazov')->type('varchar')->size(60)->null(false);
$skladkaVycisteneModel->defineColumn('obec')->type('varchar')->size(60)->null(false);
//$skladkaVycisteneModel->defineColumn('prevadzkovatel')->type('varchar')->size(60)->null(true);
$skladkaVycisteneModel->defineColumn('sidlo')->type('varchar')->size(60)->null(true);
$skladkaVycisteneModel->defineColumn('rok_zacatia')->type('datetime')->null(false);
//$skladkaVycisteneModel->defineColumn('typ')->type('tinyint')->size(1)->default(2)->null(false);
//$skladkaVycisteneModel->defineColumn('pocet_nahlaseni')->type('int')->size(4)->default(0)->null(false);
//$skladkaVycisteneModel->defineColumn('vycistena')->type('tinyint')->size(1)->default(1)->null(false);
$skladkaVycisteneModel->defineColumn('lat')->type('double')->default(0)->null(false);
$skladkaVycisteneModel->defineColumn('lng')->type('double')->default(0)->null(false);
$skladkaVycisteneModel->defineColumn('velkost')->type('double')->default(0)->null(true);
$skladkaVycisteneModel->defineColumn('id_user_cleaned')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('id_unknown_user_cleaned')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('id_unknown_user_reported')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('id_user_reported')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('created_at')->type('datetime')->null(false);
$skladkaVycisteneModel->initTable();
exit;
/** UCM_SKLADKY */
$skladkyModel = $bride->initModel('skladky');

$skladkyModel->defineColumn('okres')->type('varchar')->size(60)->null(false);
$skladkyModel->defineColumn('kraj')->type('varchar')->size(60)->null(false);
$skladkyModel->defineColumn('nazov')->type('varchar')->size(60)->null(false);
$skladkyModel->defineColumn('obec')->type('varchar')->size(60)->null(false);
// DEPRECATED 0.21: $skladkyModel->defineColumn('trieda')->type('varchar')->size(15)->null(true);
$skladkyModel->defineColumn('prevadzkovatel')->type('varchar')->size(60)->null(true);
$skladkyModel->defineColumn('sidlo')->type('varchar')->size(60)->null(true);
$skladkyModel->defineColumn('rok_zacatia')->type('datetime')->null(false);
$skladkyModel->defineColumn('typ')->type('tinyint')->size(1)->default(2)->null(false);
$skladkyModel->defineColumn('pocet_nahlaseni')->type('int')->size(4)->default(0)->null(false);
$skladkyModel->defineColumn('vycistena')->type('tinyint')->size(1)->default(0)->null(false);
$skladkyModel->defineColumn('lat')->type('double')->default(0)->null(false);
$skladkyModel->defineColumn('lng')->type('double')->default(0)->null(false);
$skladkyModel->defineColumn('velkost')->type('double')->default(0)->null(true);
$skladkyModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(true);
$skladkyModel->defineColumn('id_user')->type('int')->size(11)->null(true);
$skladkyModel->initTable();

for($i=0;$i<10;$i++) {
  $uid = uniqid();
  $skladkyModel->insert([
    'kraj' => $uid . '_kraj',
    'okres' => $uid . '_okres',
    'nazov' => $uid . '_nazov',
    'obec' => $uid . '_obec',
    'prevadzkovatel' => $uid . '_prevadzkovatel',
    'sidlo' => $uid . '_sidlo',
    'rok_zacatia' => date('Y-m-d H:i:s'),
    'typ' => 1,
    'pocet_nahlaseni' => 0,
    'lat' => 48.5378458 + (rand(100, 1000) / 100000),
    'lng' => 18.7923297 + (rand(100, 1000) / 100000)
  ]);
}

/** UCM_SKLADKY_TYPY */
$skladkaTypModel = $bride->initModel('skladky_typy');

$skladkaTypModel->defineColumn('nazov')->type('varchar')->size(25)->null(false);
$skladkaTypModel->initTable();

$typy = ['Bio', 'Papier', 'Plast', 'Kov', 'Sklo', 'Elektro', 'Zmiešaný', 'Iný'];

foreach ($typy as $typ) {
  $skladkaTypModel->insert([
    'nazov' => $typ
  ]);

  echo '<small>Inserted record ' . $typ . '</small></br>';
}

/** UCM_UNKNOWN_USERS */
$unknownUserModel = $bride->initModel('unknown_users');

$unknownUserModel->defineColumn('uid')->type('varchar')->size(30)->null(false);
$unknownUserModel->defineColumn('id_user')->type('int')->size(11)->null(true);
$unknownUserModel->defineColumn('created_at')->type('datetime')->null(false);
$unknownUserModel->defineColumn('last_login')->type('datetime')->null(true);
$unknownUserModel->initTable();

/** UCM_SKLADKY_UNKNOWN_USERS */
/*$skladkaUnknownUserModel = $bride->initModel('skladky_unknown_users');

$skladkaUnknownUserModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaUnknownUserModel->defineColumn('unknown_user_id')->type('int')->size(11)->null(false);
$skladkaUnknownUserModel->defineColumn('id_user')->type('int')->size(11)->null(true);
$skladkaUnknownUserModel->initTable();*/

/** UCM_SKLADKY_TYPY_CROSS */
$skladkaTypCrossModel = $bride->initModel('skladky_typy_cross');

$skladkaTypCrossModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaTypCrossModel->defineColumn('id_skladka_typ')->type('int')->size(11)->null(false);
$skladkaTypCrossModel->defineColumn('pocet_potvrdeni')->type('int')->size(4)->default(0)->null(false);
$skladkaTypCrossModel->defineColumn('id_unknown_user')->type('varchar')->size(30)->null(false);
$skladkaTypCrossModel->initTable();

/** UCM_SKLADKY_POTVRDENIA */
$skladkaPotvrdenieModel = $bride->initModel('skladky_potvrdenia');

$skladkaPotvrdenieModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaPotvrdenieModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(true);
$skladkaPotvrdenieModel->defineColumn('id_user')->type('int')->size(11)->null(true);
$skladkaPotvrdenieModel->initTable();

/** UCM_USERS */
$userModel = $bride->initModel('users');

$userModel->defineColumn('email')->type('varchar')->size(100)->null(false);
$userModel->defineColumn('name')->type('varchar')->size(50)->null(true);
$userModel->defineColumn('password')->type('varchar')->size(255)->null(false);
$userModel->defineColumn('type')->type('tinyint')->size(1)->default(1)->null(false);
$userModel->defineColumn('verified')->type('tinyint')->size(1)->default(0)->null(false);
$userModel->defineColumn('created_at')->type('datetime')->null(false);
$userModel->defineColumn('last_login')->type('datetime')->null(true);
$userModel->initTable();

/** UCM_TOKENS */
$tokenModel = $bride->initModel('tokens');

$tokenModel->defineColumn('type')->type('tinyint')->size(1)->null(false);
$tokenModel->defineColumn('token_number')->type('int')->size(4)->null(true);
$tokenModel->defineColumn('token_string')->type('varchar')->size(20)->null(true);
$tokenModel->defineColumn('id_user')->type('int')->size(11)->null(false);
$tokenModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(false);
$tokenModel->defineColumn('attempt')->type('tinyint')->size(1)->null(false);
$tokenModel->defineColumn('created_at')->type('datetime')->null(false);
$tokenModel->initTable();

/** UCM_GALLERY */
$galleryModel = $bride->initModel('gallery');

$galleryModel->defineColumn('link')->type('varchar')->size(100)->null(true);
$galleryModel->defineColumn('created_at')->type('datetime')->null(false);
$galleryModel->initTable();

/** UCM_SKLADKY_GALLERY */
$skladkyGalleryModel = $bride->initModel('skladky_gallery');

$skladkyGalleryModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkyGalleryModel->defineColumn('id_gallery')->type('int')->size(11)->null(false);
$skladkyGalleryModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(false);
$skladkyGalleryModel->initTable();

/** UCM_SKLADKY_NAHLASENIA */
$skladkaNahlaseniaModel = $bride->initModel('skladky_nahlasenia');

$skladkaNahlaseniaModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaNahlaseniaModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(false);
$skladkaNahlaseniaModel->initTable();

/** UCM_SKLADKY_VYCISTENE */
$skladkaVycisteneModel = $bride->initModel('skladky_vycistene');

$skladkaVycisteneModel->defineColumn('id_skladka')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('okres')->type('varchar')->size(60)->null(false);
$skladkaVycisteneModel->defineColumn('kraj')->type('varchar')->size(60)->null(false);
//$skladkaVycisteneModel->defineColumn('nazov')->type('varchar')->size(60)->null(false);
$skladkaVycisteneModel->defineColumn('obec')->type('varchar')->size(60)->null(false);
//$skladkaVycisteneModel->defineColumn('prevadzkovatel')->type('varchar')->size(60)->null(true);
$skladkaVycisteneModel->defineColumn('sidlo')->type('varchar')->size(60)->null(true);
$skladkaVycisteneModel->defineColumn('rok_zacatia')->type('datetime')->null(false);
//$skladkaVycisteneModel->defineColumn('typ')->type('tinyint')->size(1)->default(2)->null(false);
//$skladkaVycisteneModel->defineColumn('pocet_nahlaseni')->type('int')->size(4)->default(0)->null(false);
//$skladkaVycisteneModel->defineColumn('vycistena')->type('tinyint')->size(1)->default(1)->null(false);
$skladkaVycisteneModel->defineColumn('lat')->type('double')->default(0)->null(false);
$skladkaVycisteneModel->defineColumn('lng')->type('double')->default(0)->null(false);
$skladkaVycisteneModel->defineColumn('velkost')->type('double')->default(0)->null(true);
$skladkaVycisteneModel->defineColumn('id_user_cleaned')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('id_unknown_user_cleaned')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('id_unknown_user_reported')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('id_user_reported')->type('int')->size(11)->null(true);
$skladkaVycisteneModel->defineColumn('created_at')->type('datetime')->null(false);
$skladkaVycisteneModel->initTable();

/** UCM_SKLADKY_VYCISTENE_GALLERY */
$skladkyGalleryModel = $bride->initModel('skladky_vycistene_gallery');

$skladkyGalleryModel->defineColumn('id_skladka_vycistena')->type('int')->size(11)->null(false);
$skladkyGalleryModel->defineColumn('id_gallery')->type('int')->size(11)->null(false);
$skladkyGalleryModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(false);
$skladkyGalleryModel->initTable();

/** UCM_NOTIFICATIONS */
/*$skladkaPotvrdenieModel = $bride->initModel('notifications');

$skladkaPotvrdenieModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaPotvrdenieModel->defineColumn('unknown_user_uid')->type('varchar')->size(30)->null(false);
$skladkaPotvrdenieModel->initTable();*/

/** UCM_DEVICES_LOGS */
$deviceLogModel = $bride->initModel('devices_logs');

$deviceLogModel->defineColumn('device_type')->type('int')->size(1)->null(false);
$deviceLogModel->defineColumn('page')->type('varchar')->size(30)->null(false);
$deviceLogModel->defineColumn('created_at')->type('datetime')->null(false);
$deviceLogModel->initTable();

/** UCM_APP_TEXTS */
$appTextModel = $bride->initModel('app_texts');

$appTextModel->defineColumn('device_type')->type('int')->size(1)->null(false)->default(1);
$appTextModel->defineColumn('page')->type('varchar')->size(30)->null(false);
$appTextModel->defineColumn('type')->type('varchar')->size(30)->null(false);
$appTextModel->defineColumn('text')->type('varchar')->size(100)->null(false);
$appTextModel->initTable();

$commonTexts = [
  'Aktuálna verzia TrashRunnera: TEST',
  'Nahlasujete nelegálne skládky ihneď',
  'Prehľadávajte už nahlásené skládky',
  'Máte nápad? Kontaktujte nás'
];

foreach ($commonTexts as $text) {
  $appTextModel->insert([
    'device_type' => 1,
    'page' => 'home',
    'type' => 'welcome',
    'text' => $text
  ]);

  $appTextModel->insert([
    'device_type' => 2,
    'page' => 'home',
    'type' => 'welcome',
    'text' => $text
  ]);
}

/** UCM_IDEAS */
$ideaModel = $bride->initModel('ideas');

$ideaModel->defineColumn('email')->type('varchar')->size(50)->null(false);
$ideaModel->defineColumn('image')->type('varchar')->size(50)->null(true);
$ideaModel->defineColumn('device_type')->type('int')->size(1)->null(false)->default(1);
$ideaModel->defineColumn('type')->type('int')->size(1)->null(false)->default(1);
$ideaModel->defineColumn('text')->type('text')->null(false);
$ideaModel->initTable();

/** UCM_UNKNOWN_USERS_USERS_CROSS */
$unknownUserUserCrossModel = $bride->initModel('unknown_users_users_cross');

$unknownUserUserCrossModel->defineColumn('id_unknown_user')->type('int')->size(11)->null(false);
$unknownUserUserCrossModel->defineColumn('id_user')->type('int')->size(11)->null(false);

$unknownUserUserCrossModel->initTable();

/** UCM_ML_IMAGES */
$mcImageModel = $bride->initModel('ml_images');

$mcImageModel->defineColumn('index')->type('int')->size(8)->null(false);
$mcImageModel->defineColumn('text')->type('varchar')->size(50)->null(false);
$mcImageModel->defineColumn('confidence')->type('double')->null(false);

$mcImageModel->initTable();

/**
 * CLEAR ___FILES DIR
*/

function rrmdir(string $directory): bool {
  array_map(fn (string $file) => is_dir($file) ? rrmdir($file) : unlink($file), glob($directory . '/' . '*'));

  return rmdir($directory);
}

if (rrmdir(FILES_DIR)) {
  echo "Úspešne odstránene ___files <br>";
  if (mkdir(FILES_DIR)) {
    echo "Uspesne vytvorene ___files <br>";
    if (mkdir(FILES_DIR . '/nelegalne-skladky')) {
      echo "Uspesne vytvorene ___files/nelegalne-skladky <br>";
    } else {
      echo "Nastala chyba pri vytvarani ___files/nelegalne-skladky<br>";
    }
  } else {
    echo "Nastala chyba pri vytvarani ___files<br>";
  }
} else {
  echo "Nastala chyba pri odstraneni ___files<br>";
}