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
      $vsetkySkladky,
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

  /**
   * @param array $mixedData
   * @return array skladky data
   */
  public function getSkladkyData(array $vsetkySkladky, array $mixedData) : array {
    $skladky = [];

    $mixedDataIds = [];
    $mixedDataImproved = [];
    foreach ($mixedData as $mixedItem) {
      $mixedDataIds[] = $mixedItem["id"];
      $mixedDataImproved[$mixedItem["id"]] = $mixedItem;
    }

    foreach ($vsetkySkladky as $skladka) {
      if (in_array($skladka["id"], $mixedDataIds)) {
        if (!isset($skladky[$skladka["id"]])) {
          $skladky[$skladka["id"]] = array_merge(
            [
              "id" => $this->getMixedDataValue($mixedDataImproved, "id", $skladka["id"]),
              "okres" => $this->getMixedDataValue($mixedDataImproved, "okres", $skladka["id"]),
              "nazov" => $this->getMixedDataValue($mixedDataImproved, "nazov", $skladka["id"]),
              "obec" => $this->getMixedDataValue($mixedDataImproved, "obec", $skladka["id"]),
              "trieda" => $this->getMixedDataValue($mixedDataImproved, "trieda", $skladka["id"]),
              "prevadzkovatel" => $this->getMixedDataValue($mixedDataImproved, "prevadzkovatel", $skladka["id"]),
              "sidlo" => $this->getMixedDataValue($mixedDataImproved, "sidlo", $skladka["id"]),
              "rok_zacatia" => $this->getMixedDataValue($mixedDataImproved, "rok_zacatia", $skladka["id"]),
              "typ" => $this->getMixedDataValue($mixedDataImproved, "typ", $skladka["id"]),
              "pocet_nahlaseni" => $this->getMixedDataValue($mixedDataImproved, "pocet_nahlaseni", $skladka["id"]),
              "existujuca" => $this->getMixedDataValue($mixedDataImproved, "existujuca", $skladka["id"]),
              "lat" => $this->getMixedDataValue($mixedDataImproved, "lat", $skladka["id"]),
              "lng" => $this->getMixedDataValue($mixedDataImproved, "lng", $skladka["id"]),
            ], 
            Helper::getSkladkaTyp(
              $this->getMixedDataValue($mixedDataImproved, "typ_skladky", $skladka["id"]),
              $this->getMixedDataValue($mixedDataImproved, "pocet_potvrdeni", $skladka["id"])
            )
          );
        } else {
          $skladky[$skladka["id"]] = array_merge(
            $skladky[$skladka["id"]],
            Helper::getSkladkaTyp(
              $this->getMixedDataValue($mixedDataImproved, "typ_skladky", $skladka["id"]),
              $this->getMixedDataValue($mixedDataImproved, "pocet_potvrdeni", $skladka["id"])
            )
          );
        }
      } else {
        $skladky[$skladka["id"]] = $skladka;
      }
    }

    return array_values($skladky);
  }

  public function getMixedDataValue(array $mixedDataImproved, string $itemColumn, int $skladkaId) {
    return $mixedDataImproved[$skladkaId][$itemColumn];
  }
}