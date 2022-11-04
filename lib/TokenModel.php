<?php

class TokenModel extends Model {

  public string $tableName = "ucm_tokens";

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