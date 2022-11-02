<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config.php');

use \RedBeanPHP\R as R;

R::setup(
  "mysql:host=localhost;dbname=" . DB_NAME,
  DB_USER, DB_PASSWORD
);

R::ext('ucmdispense', function( $type ){ 
  return R::getRedBean()->dispense( $type ); 
});

/** UCM_NOTIFICATIONS */
$notifications = R::ucmdispense('ucm_notifications');

$notifications->title = 'Testovacia notifikacia #1';
$notifications->description = 'Testovacia notifikacia popis #1';
$notifications->type = 1;
$notifications->created_at = date('Y-m-d H:i:s', time());

R::store($notifications);

/** UCM_SKLADKY */
$skladka1 = R::ucmdispense('ucm_skladky');

$skladka1->okres = 'Legalna skladka #1';
$skladka1->nazov = 'Testovacia notifikacia popis #1';
$skladka1->obec = 'Trnava';
$skladka1->trieda = 'XXXX';
$skladka1->prevadzkovatel = 'Legalna skladka prevadzkovatel #1';
$skladka1->sidlo = 'Legalna skladka sidlo #1';
$skladka1->rok_zacatia = date('Y-m-d H:i:s', time());
$skladka1->typ = 1;
$skladka1->pocet_nahlaseni = 0;
$skladka1->existujuca = 1;
$skladka1->lat = 48.1833;
$skladka1->lng = 17.0379;

R::store($skladka1);

$skladka2 = R::ucmdispense('ucm_skladky');

$skladka2->okres = 'Nelegalna skladka #1';
$skladka2->nazov = 'Testovacia notifikacia popis #1';
$skladka2->obec = 'Trnava';
$skladka2->rok_zacatia = date('Y-m-d H:i:s', time());
$skladka2->typ = 2;
$skladka2->existujuca = 1;
$skladka2->lat = 48.384046250301;
$skladka2->lng = 17.587909698486;

R::store($skladka2);

/** UCM_SKLADKY_TYPY */
$skladkaTypyArray = [
  'Bio odpad',
  'Papier',
  'Plast',
  'Olej',
  'Sklo',
  'Elektro',
  'Zmiesany',
  'Iny'
];

foreach ($skladkaTypyArray as $skladkaTyp) {
  $skladkyTypy = R::ucmdispense('ucm_skladky_typy');
  $skladkyTypy->nazov = $skladkaTyp;
  R::store($skladkyTypy);
}

/** UCM_SKLADKY_TYPY_CROSS */
$skladkaTypCross = R::ucmdispense('ucm_skladky_cross');

$skladkaTypCross->id_skladka = 2;
$skladkaTypCross->id_skladka_typ = 1;
$skladkaTypCross->pocet_potvrdeni = 10;

R::store($skladkaTypCross);

/** UCM_UNKNOWN_USERS */
$ucmUnknownUsers1 = R::ucmdispense('ucm_unknown_users');

$reportUserUid = uniqid();
$ucmUnknownUsers1->uid = $reportUserUid;
$ucmUnknownUsers1->created_at = date('Y-m-d H:i:s', time());
$ucmUnknownUsers1->last_login = date('Y-m-d H:i:s', time());

R::store($ucmUnknownUsers1);

$ucmUnknownUsers2 = R::ucmdispense('ucm_unknown_users');

$acceptUserUid = uniqid();
$ucmUnknownUsers2->uid = $acceptUserUid;
$ucmUnknownUsers2->created_at = date('Y-m-d H:i:s', time());
$ucmUnknownUsers2->last_login = date('Y-m-d H:i:s', time());

R::store($ucmUnknownUsers2);

/** UCM_SKLADKY_UNKNOWN_USERS */
$ucmSkladkyUnknownUsers = R::ucmdispense('ucm_skladky_unknown_users');

$ucmSkladkyUnknownUsers->id_skladka = 2;
$ucmSkladkyUnknownUsers->unknown_user_uid = $reportUserUid;

R::store($ucmSkladkyUnknownUsers);

/** UCM_SKLADKY_POTVRDENIA */
$ucmSkladkyPotvrdenia = R::ucmdispense('ucm_skladky_potvrdenia');

$ucmSkladkyPotvrdenia->id_skladka = 2;
$ucmSkladkyPotvrdenia->unknown_user_uid = $acceptUserUid;

R::store($ucmSkladkyPotvrdenia);


