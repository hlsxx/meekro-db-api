<?php

abstract class Model {

  public string $tableName = "";

  /**
   * @return array data
   */
  public function getAll() {
    return DB::query("SELECT * FROM {$this->tableName}");
  }

  /**
   * @param int $id
   * @return array data
   */
  public function getById(int $id) {
    return DB::query("SELECT * FROM {$this->tableName} WHERE id = %d", $id);
  }

  /**
   * @param array data to insert
   * @return int created record id
   */
  public function insert(array $dataToInsert) {
    return DB::insertId($dataToInsert, $this->tableName);
  }

}