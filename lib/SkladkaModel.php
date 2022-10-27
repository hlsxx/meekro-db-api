<?php

class SkladkaModel extends Model {

  public string $tableName = "ucm_skladky";

  private int $currentLoopSkladkaId;
  private array $mixedDataImproved;
  
  /**
   * @return array data from ucm_skladky
   */
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
   * @return array data pagination data from ucm_skladky
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
   * @param array $filterData FILTER data
   * @return array data pagination data from ucm_skladky FILTERED
   */
  public function getPaginationDataFiltered(array $filterData): array {

    $data = DB::query(
      "SELECT 
        *
      FROM {$this->tableName}
      WHERE {$this->tableName}.typ IN (%d, %d)
      ORDER BY id DESC
      LIMIT %d, %d",
      !in_array((int)$filterData["type"], [1, 2]) ? 1 : (int)$filterData["type"],
      !in_array((int)$filterData["type"], [1, 2]) ? 2 : (int)$filterData["type"],
      Helper::getOffset(),
      Helper::$itemsPerPage
    );

    $filterDataByDistance = [];
    if ($filterData["gpsEnabled"] === true && (int)$filterData["filterBy"] == 2 && (int)$filterData["distance"] > 0) {

      foreach ($data as $skladka) {
        $distance = Helper::getDistanceFromLatLonInKm(
          (float)$filterData["lat"],
          (float)$filterData["lng"],
          (float)$skladka["lat"], 
          (float)$skladka["lng"]
        );

        // Check distance from FILTERED value
        if ($distance <= (int)$filterData["distance"]) {
          $filterDataByDistance[] = $skladka;
        }
      }
    }

    return empty($filterDataByDistance) ? $data : $filterDataByDistance;
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
              (int)$this->getMixedDataValue("typ_skladky"),
              (int)$this->getMixedDataValue("pocet_potvrdeni")
            )
          );
        } else {
          $skladky[$skladka["id"]] = array_merge(
            $skladky[$skladka["id"]],
            Helper::getSkladkaTyp(
              (int)$this->getMixedDataValue("typ_skladky"),
              (int)$this->getMixedDataValue("pocet_potvrdeni")
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
   * @param array $filterData FILTER data
   * @return array all data from ucm_skladky FILTERED
   */
  public function getAllFiltered(array $filterData): array {
    return DB::query(
      "SELECT 
        *
      FROM {$this->tableName}
      WHERE {$this->tableName}.typ IN (%d, %d)
      ORDER BY id DESC",
      !in_array((int)$filterData["type"], [1, 2]) ? 1 : (int)$filterData["type"],
      !in_array((int)$filterData["type"], [1, 2]) ? 2 : (int)$filterData["type"]
    );
  }

  /**
   * @param string $itemColumn
   * @return string|int|double 
   */
  public function getMixedDataValue(string $itemColumn) {
    return $this->mixedDataImproved[$this->currentLoopSkladkaId][$itemColumn];
  }

  /**
   * @param int $id
   * @return array data
   */
  public function getByIdComplex(int $id, int $type = NULL): array {
    $skladkaTypyCrossModel = new SkladkaTypCrossModel();
    $skladkaTypModel = new SkladkaTypModel();

    if ($type === 2) {
      return DB::queryFirstRow("
        SELECT 
          skladky.*,
          skladky_typy_cross.pocet_potvrdeni,
          skladky_typy_cross.popis,
          skladky_typy.nazov as skladka_typ_nazov
        FROM {$skladkaTypyCrossModel->tableName} as skladky_typy_cross
        LEFT JOIN {$this->tableName} as skladky
        ON skladky.id = skladky_typy_cross.id_skladka
        LEFT JOIN {$skladkaTypModel->tableName} as skladky_typy
        ON skladky_typy.id = skladky_typy_cross.id_skladka_typ
        WHERE skladky_typy_cross.id_skladka = %d
      ", $id);
    } else if ($type === 1) {
      return parent::getById($id);
    }
  }

  /**
   * @param float $lat
   * @param float $lng
   * @return array data
   */
  public function getByCoors(float $lat, float $lng): array {
    return DB::queryFirstRow("SELECT * FROM {$this->tableName} WHERE lat = %d AND lng = %d", $lat, $lng);
  }

  /**
   * @param float $lat
   * @param float $lng
   * @return array data
   */
  public function getByCoorsComplex(float $lat, float $lng): array {
    $skladkaUnknownUserModel = new SkladkaUnknownUserModel();

    return DB::queryFirstRow("
      SELECT 
        s.*,
        sus.unknown_user_uid as uid 
      FROM {$this->tableName} s
      LEFT JOIN {$skladkaUnknownUserModel->tableName} sus
      ON sus.id_skladka = s.id
      WHERE lat = %d AND lng = %d
    ", $lat, $lng);
  }
}