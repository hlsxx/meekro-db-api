<?php

if(php_sapi_name() !== "cli") {
  header("HTTP/1.0 404 Not Found");
  exit;
}

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

  $tokenModel = $bride->initModel('tokens');
  $tokenModel->query("TRUNCATE {model}");
} catch(Exception $e) {
  $logError = new Monolog\Logger('CRON');
  $logError->pushHandler(
    new Monolog\Handler\StreamHandler(__DIR__ . '/../logs/cron-error.log', Monolog\Logger::ERROR)
  );

  $logInfo->info("CRON " . date('d.m.Y H:i:s'));
}

