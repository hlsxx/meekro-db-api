<?php

class SkladkaTypModel extends Model {

  public string $tableName = "ucm_skladky_typy";

  public static array $types = [
    "legalna" => 1,
    "nelegalna" => 2
  ];

}