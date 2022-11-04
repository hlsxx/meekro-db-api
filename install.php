<?php

require_once('./config.php');

$bride = new \Test\BridePHP(DB_NAME, DB_USER, DB_PASSWORD);

$bride->tablePrefix('ucm');

exit();
/** UCM_SKLADKY */
$skladkyModel = $bride->initModel('skladky');

$skladkyModel->defineColumn('okres')->type('varchar')->size(60)->null(false);
$skladkyModel->defineColumn('nazov')->type('varchar')->size(60)->null(false);
$skladkyModel->defineColumn('obec')->type('varchar')->size(60)->null(false);
$skladkyModel->defineColumn('trieda')->type('varchar')->size(15)->null(true);
$skladkyModel->defineColumn('prevadzkovatel')->type('varchar')->size(60)->null(true);
$skladkyModel->defineColumn('sidlo')->type('varchar')->size(60)->null(true);
$skladkyModel->defineColumn('rok_zacatia')->type('datetime')->null(false);
$skladkyModel->defineColumn('typ')->type('tinyint')->size(1)->default(2)->null(false);
$skladkyModel->defineColumn('pocet_nahlaseni')->type('int')->size(4)->default(0)->null(false);
$skladkyModel->defineColumn('existujuca')->type('tinyint')->size(1)->default(1)->null(false);
$skladkyModel->defineColumn('lat')->type('double')->default(0)->null(false);
$skladkyModel->defineColumn('lng')->type('double')->default(0)->null(false);
$skladkyModel->initTable();

/** UCM_SKLADKY_TYPY */
$skladkaTypModel = $bride->initModel('skladky_typy');

$skladkaTypModel->defineColumn('nazov')->type('varchar')->size(25)->null(false);
$skladkaTypModel->initTable();

/** UCM_UNKNOWN_USERS */
$unknownUserModel = $bride->initModel('unknown_users');

$unknownUserModel->defineColumn('uid')->type('varchar')->size(30)->null(false);
$unknownUserModel->defineColumn('id_user')->type('int')->size(11)->null(false);
$unknownUserModel->defineColumn('created_at')->type('datetime')->null(false);
$unknownUserModel->defineColumn('last_login')->type('datetime')->null(true);
$unknownUserModel->initTable();

/** UCM_SKLADKY_UNKNOWN_USERS */
$skladkaUnknownUserModel = $bride->initModel('skladky_unknown_users');

$skladkaUnknownUserModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaUnknownUserModel->defineColumn('unknown_user_uid')->type('varchar')->size(30)->null(false);
$skladkaUnknownUserModel->initTable();

/** UCM_SKLADKY_TYPY_CROSS */
$skladkaTypCrossModel = $bride->initModel('ucm_skladky_typy_cross');

$skladkaTypCrossModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaTypCrossModel->defineColumn('id_skladka_typ')->type('int')->size(11)->null(false);
$skladkaTypCrossModel->defineColumn('pocet_potvrdeni')->type('int')->size(4)->default(0)->null(false);
$skladkaTypCrossModel->defineColumn('unknown_user_uid')->type('varchar')->size(30)->null(false);
$skladkaTypCrossModel->initTable();

/** UCM_SKLADKY_POTVRDENIA */
$skladkaPotvrdenieModel = $bride->initModel('skladky_potvrdenia');

$skladkaPotvrdenieModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaPotvrdenieModel->defineColumn('unknown_user_uid')->type('varchar')->size(30)->null(false);
$skladkaPotvrdenieModel->initTable();

/** UCM_USERS */
$userModel = $bride->initModel('users');

//$userModel->defineColumn('unknown_user_id')->type('int')->size(11)->null(false);
$userModel->defineColumn('email')->type('varchar')->size(100)->null(false);
$userModel->defineColumn('name')->type('varchar')->size(50)->null(true);
$userModel->defineColumn('password')->type('varchar')->size(255)->null(false);
$userModel->defineColumn('type')->type('tinyint')->size(1)->default(1)->null(false);
$userModel->defineColumn('verified')->type('tinyint')->size(1)->default(0)->null(false);
$userModel->initTable();

/** UCM_TOKENS */
$tokenModel = $bride->initModel('tokens');

$tokenModel->defineColumn('type')->type('tinyint')->size(1)->null(false);
$tokenModel->defineColumn('token_number')->type('int')->size(4)->null(true);
$tokenModel->defineColumn('token_string')->type('varchar')->size(20)->null(true);
$tokenModel->defineColumn('id_user')->type('int')->size(11)->null(false);
$tokenModel->defineColumn('attempt')->type('tinyint')->size(1)->null(false);
$tokenModel->initTable();


/** UCM_NOTIFICATIONS */
/*$skladkaPotvrdenieModel = $bride->initModel('notifications');

$skladkaPotvrdenieModel->defineColumn('id_skladka')->type('int')->size(11)->null(false);
$skladkaPotvrdenieModel->defineColumn('unknown_user_uid')->type('varchar')->size(30)->null(false);
$skladkaPotvrdenieModel->initTable();*/