<?php

class SkladkaModel extends Model {

  public string $tableName = "ucm_skladky";
  
  public function getAll() {
    $skladkaTypyCrossModel = new SkladkaTypCrossModel();
    $skladkaTypModel = new SkladkaTypModel();

    $mixedData = DB::query("
      SELECT 
        skladky.*,
        skladky_typy.id as typ_skladky
      FROM {$skladkaTypyCrossModel->tableName} as skladky_typy_cross
      LEFT JOIN {$this->tableName} as skladky
        ON skladky.id = skladky_typy_cross.id_skladka
      LEFT JOIN {$skladkaTypModel->tableName} as skladky_typy
        ON skladky_typy.id = skladky_typy_cross.id_skladka_typ
      ORDER BY id DESC"
    );

    $skladky = [];
    foreach ($mixedData as $item) {
      if (!isset($skladky[$item["id"]])) {
        $skladky[$item["id"]] = [
          "okres" => $item["okres"],
          "nazov" => $item["nazov"],
          "obec" => $item["obec"],
          "trieda" => $item["trieda"],
          "prevadzkovatel" => $item["prevadzkovatel"],
          "sidlo" => $item["sidlo"],
          "rok_zacatia" => $item["rok_zacatia"],
          "typ" => $item["typ"],
          "pocet_nahlaseni" => $item["pocet_nahlaseni"],
          "existujuca" => $item["existujuca"],
          "lat" => $item["lat"],
          "lng" => $item["lng"],
          "bio" => Helper::getSkladkaTyp($item["typ_skladky"], "bio"),
          "papier" => "",
          "plast" => "",
          "olej" => "",
          "sklo" => "",
          "elektro" =>"",
          "zmiesane" => "",
          "vlastne" => ""
        ];
      } else {
        //$skladky[$item["id"]]["typ_skladky"][] = $item["typ_skladky"];
      }
    }

    return $skladky;
  }
}