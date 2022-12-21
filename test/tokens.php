<?php

  require_once(__DIR__ . '/../vendor/autoload.php');
  require_once(__DIR__ . '/../config.php');
  
  DB::$user = DB_USER;
  DB::$password = DB_PASSWORD;
  DB::$dbName = DB_NAME;
  
  $bride = new \Bride\Bride(DB_NAME, DB_USER, DB_PASSWORD);
  $bride->tablePrefix('ucm');

  $tokenModel = $bride->initModel('tokens');
  $tokensData = $tokenModel->getAll();

  $html = "";
  foreach ($tokensData as $tokenData) {
    $html .= "
      <li class='list-group-item'>
        <span class='badge bg-danger me-5' style='font-size: 18px'>{$tokenData['token_number']}</span>
        Zostavajúcich pokusov: {$tokenData['attempt']}	
        <b class='ms-5'>Vytvorený: {$tokenData['created_at']}</b>
      </li>
    ";
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TOKENS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
  <div class="container mb-5">
    <div class="card bg-secondary card-body mt-5">
    <ul class="list-group"><?php echo $html ?></ul>
    </div>
  </div>
</body>
</html>