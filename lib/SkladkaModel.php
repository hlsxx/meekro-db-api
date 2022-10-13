<?php

class SkladkaModel extends Model {

  public string $tableName = "ucm_skladky";

  private int $currentLoopSkladkaId;
  private array $mixedDataImproved;
  
  public function getAllComplex() : array {
    $skladkaTypyCrossModel = new SkladkaTypCrossModel();
    $skladkaTypModel = new SkladkaTypModel();

    $vsetkySkladky = parent::getAll();
    
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
      ),
      $vsetkySkladky
    );
  }

  /**
   * @return array data from ucm_skladky
   */
  public function getPaginationDataComplex() : array {
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
      "),
      $vsetkySkladky
    );
  }

  /**
   * @param array $mixedData
   * @return array skladky data
   */
  public function getSkladkyData(array $mixedData, array $vsetkySkladky) : array {
    $skladky = [];

    $mixedDataIds = [];
    foreach ($mixedData as $mixedItem) {
      $mixedDataIds[] = $mixedItem["id"];
      $this->mixedDataImproved[$mixedItem["id"]] = $mixedItem;
    }

    foreach ($vsetkySkladky as $skladka) {
      $this->currentLoopSkladkaId = $skladka["id"];

      if (in_array($this->currentLoopSkladkaId, $mixedDataIds)) {
        if (!isset($skladky[$this->currentLoopSkladkaId])) {
          $skladky[$this->currentLoopSkladkaId] = array_merge(
            [
              "id" => $this->getMixedDataValue("id"),
              "okres" => $this->getMixedDataValue("okres"),
              "nazov" => $this->getMixedDataValue("nazov"),
              "obec" => $this->getMixedDataValue("obec"),
              "trieda" => $this->getMixedDataValue("trieda"),
              "prevadzkovatel" => $this->getMixedDataValue("prevadzkovatel"),
              "sidlo" => $this->getMixedDataValue("sidlo"),
              "rok_zacatia" => $this->getMixedDataValue("rok_zacatia"),
              "typ" => $this->getMixedDataValue("typ"),
              "pocet_nahlaseni" => $this->getMixedDataValue("pocet_nahlaseni"),
              "existujuca" => $this->getMixedDataValue("existujuca"),
              "lat" => $this->getMixedDataValue("lat"),
              "lng" => $this->getMixedDataValue("lng"),
            ], 
            Helper::getSkladkaTyp(
              $this->getMixedDataValue("typ_skladky"),
              $this->getMixedDataValue("pocet_potvrdeni")
            )
          );
        } else {
          $skladky[$skladka["id"]] = array_merge(
            $skladky[$skladka["id"]],
            Helper::getSkladkaTyp(
              $this->getMixedDataValue("typ_skladky"),
              $this->getMixedDataValue("pocet_potvrdeni")
            )
          );
        }
      } else {
        $skladky[$skladka["id"]] = $skladka;
      }
    }

    return array_values($skladky);
  }

  /**
   * @param string $itemColumn
   * @return string|int|double 
   */
  public function getMixedDataValue(string $itemColumn) {
    return $this->mixedDataImproved[$this->currentLoopSkladkaId][$itemColumn];
  }
}