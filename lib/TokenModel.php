<?php

class TokenModel extends Model {

  public string $tableName = "ucm_tokens";

  public static array $types = [
    "registration" => 1,
    "forgotten_password" => 2
  ];

  public function getTokenNumber() {
    $tokenNumber = rand(1000, 9999);

    $check = DB::query(
      "SELECT * FROM {$this->tableName} WHERE token_number = {$tokenNumber}"
    );

    if (!empty($check)) {
      $tokenNumber = $this->getTokenNumber();
    }

    return $tokenNumber;
  }

}