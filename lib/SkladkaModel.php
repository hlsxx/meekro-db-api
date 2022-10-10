<?php

class SkladkaModel extends Model {

  public string $tableName = "ucm_skladky";
  
  public function getAll() : array {
    $skladkaTypyCrossModel = new SkladkaTypCrossModel();
    $skladkaTypModel = new SkladkaTypModel();

    return $this->getSkladkyData(
      DB::query("
        SELECT 
          skladky.*,
          skladky_typy.id as typ_skladky,
          skladky_typy_cross.pocet_potvrdeni
        FROM {$skladkaTypyCrossModel->tableName} as skladky_typy_cross
        LEFT JOIN {$this->tableName} as skladky
          ON skladky.id = skladky_typy_cross.id_skladka
        LEFT JOIN {$skladkaTypModel->tableName} as skladky_typy
          ON skladky_typy.id = skladky_typy_cross.id_skladka_typ
        ORDER BY id DESC"
      )
    );
  }

  /**
   * @return array data from ucm_skladky
   */
  public function getPaginationData() : array {
    $skladkaTypyCrossModel = new SkladkaTypCrossModel();
    $skladkaTypModel = new SkladkaTypModel();

    $vsetkySkladky = parent::getPaginationData();

    $skladkyIds = [];
    foreach ($vsetkySkladky as $skladka) {
      $skladkyIds[] = $skladka["id"];
    }

    return $this->getSkladkyData(
      DB::query("
        SELECT 
          skladky.*,
          skladky_typy.id as typ_skladky,
          skladky_typy_cross.pocet_potvrdeni
        FROM {$skladkaTypyCrossModel->tableName} as skladky_typy_cross
        LEFT JOIN {$this->tableName} as skladky
          ON skladky.id = skladky_typy_cross.id_skladka
        LEFT JOIN {$skladkaTypModel->tableName} as skladky_typy
          ON skladky_typy.id = skladky_typy_cross.id_skladka_typ
        WHERE skladky.id IN (".implode(",", $skladkyIds).") 
        ORDER BY id DESC
      ")
    );
  }

  public function getSkladkyData(array $mixedData) : array {
    $skladky = [];
    foreach ($mixedData as $item) {
      if (!isset($skladky[$item["id"]])) {
        $skladky[$item["id"]] = array_merge([
          "id" => $item["id"],
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
          "lng" => $item["lng"]
        ], Helper::getSkladkaTyp($item["typ_skladky"], $item["pocet_potvrdeni"]));
      } else {
        $skladky[$item["id"]] = array_merge(
          $skladky[$item["id"]],
          Helper::getSkladkaTyp($item["typ_skladky"], $item["pocet_potvrdeni"])
        );
      }
    }

    return array_values($skladky);
  }
}