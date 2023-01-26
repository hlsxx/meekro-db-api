<?php

//if (php_sapi_name() !== "cli") {
//  header("HTTP/1.0 404 Not Found");
//  exit;
//}

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config.php');

DB::$user = DB_USER;
DB::$password = DB_PASSWORD;
DB::$dbName = DB_NAME;

$bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);
$bride->tablePrefix('ucm');

try {
  $logInfo = new Monolog\Logger('CRON');
  $logInfo->pushHandler(
    new Monolog\Handler\StreamHandler(__DIR__ . '/../logs/cron-info.log', Monolog\Logger::INFO)
  );

  $logInfo->info("CRON " . date('d.m.Y H:i:s'));

  $skladkaModel = $bride->initModel('skladky');
  $skladkaVycisteniaModel = $bride->initModel('skladky_vycistene');

  $skladkyVycistene = $skladkaVycisteniaModel->query("
    SELECT
      *
    FROM {model}
    WHERE id_skladka IS NOT NULL
  ");

  foreach ($skladkyVycistene as $skladka) {
    $skladkaModel->delete((int)$skladka['id_skladka']);
    
    $skladkaVycistena = $skladkaVycisteniaModel->getById((int)$skaldka['id_skladka']);
    $skladkaVycisteniaModel->update([
      'id_skladka' => null
    ], (int)$skladka['id']);
  }
} catch(Exception $e) {
  $logError = new Monolog\Logger('CRON'); 
  $logError->pushHandler(
    new Monolog\Handler\StreamHandler(__DIR__ . '/../logs/cron-error.log', Monolog\Logger::ERROR)
  );

  $logInfo->info("CRON " . date('d.m.Y H:i:s'));
}

