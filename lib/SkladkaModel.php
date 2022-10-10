<?php

class SkladkaModel extends Model {

  public string $tableName = "ucm_skladky";
  
  public function getAll() {
    $skladkaTypyCrossModel = new SkladkaTypCrossModel();
    $skladkaTypModel = new SkladkaTypModel();

    return DB::query("
      SELECT 
        skladky.*
      FROM {$skladkaTypyCrossModel->tableName} as skladky_typy_cross
      LEFT JOIN {$this->tableName} as skladky
        ON skladky.id = skladky_typy_cross.id_skladka
      LEFT JOIN {$skladkaTypModel->tableName} as skladky_typy
        ON skladky_typy.id = skladky_typy_cross.id_skladka_typ
      ORDER BY id DESC"
    );
  }
}