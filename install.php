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
$skladky = R::ucmdispense('ucm_skladky');

$skladky->okres = 'Legalna skladka #1';
$skladky->nazov = 'Testovacia notifikacia popis #1';
$skladky->obec = 'Trnava';
$skladky->trieda = 'XXXX';
$skladky->prevadzkovatel = 'Legalna skladka prevadzkovatel #1';
$skladky->sidlo = 'Legalna skladka sidlo #1';
$skladky->rok_zacatia = date('Y-m-d H:i:s', time());
$skladky->typ = 1;
$skladky->pocet_nahlaseni = 0;
$skladky->existujuca = 1;
$skladky->lat = 48.1833;
$skladky->lng = 17.0379;

R::store($skladky);

$skladky->okres = 'Nelegalna skladka #1';
$skladky->nazov = 'Testovacia notifikacia popis #1';
$skladky->obec = 'Trnava';
$skladky->rok_zacatia = date('Y-m-d H:i:s', time());
$skladky->typ = 2;
$skladky->existujuca = 1;
$skladky->lat = 48.384046250301;
$skladky->lng = 17.587909698486;

R::store($skladky);

/** UCM_SKLADKY_TYPY */
$skladkyTypy = R::ucmdispense('ucm_skladky_typy');

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
  $skladkyTypy->nazov = $skladkaTyp;
  R::store($skladkyTypy);
}

/** UCM_SKLADKY_TYPY_CROSS */
$skladky = R::ucmdispense('ucm_skladky_cross');

$skladky->id_skladka = 'Legalna skladka #1';
$skladky->id_skladka_typ = 2;
$skladky->pocet_potvrdeni = 2;
