<?php

abstract class Model {

  public string $tableName = "";

  /**
   * @return array data
   */
  public function getAll() {
    return DB::query("SELECT * FROM {$this->tableName} ORDER BY id DESC");
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
    DB::insert($this->tableName, $dataToInsert);

    return DB::insertId();
  }

}