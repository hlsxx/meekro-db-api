<?php

abstract class Model {

  public string $tableName = "";

  public function getAll() {
    return DB::query("SELECT * FROM {$this->tableName}");
  }

  public function getById(int $id) {
    return DB::query("SELECT * FROM {$this->tableName} WHERE id = %d", $id);
  }

}