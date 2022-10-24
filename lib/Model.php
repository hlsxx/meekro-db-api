<?php

abstract class Model {

  public string $tableName = "";

  /**
   * @return array data
   */
  public function getAll() : array {
    return DB::query("SELECT * FROM {$this->tableName} ORDER BY id DESC");
  }

  /**
   * @return array data
   */
  public function getAllOrderBy(string $orderByCol = "id", string $orderByAso = "DESC") : array {
    return DB::query("SELECT * FROM {$this->tableName} ORDER BY %s %s", $orderByCol, $orderByAso);
  }

  /**
   * @return array data
   */
  public function getPaginationData() : array {
    return  DB::query("
      SELECT 
        *
      FROM {$this->tableName}
      ORDER BY id DESC
      LIMIT %d, %d
    ", 
      Helper::getOffset(),
      Helper::$itemsPerPage
    );
  }

  /**
   * @param int $id
   * @return array data
   */
  public function getById(int $id) : array {
    return DB::queryFirstRow("SELECT * FROM {$this->tableName} WHERE id = %d", $id);
  }

  /**
   * @param array data to insert
   * @return int created record id
   */
  public function insert(array $dataToInsert) : int {
    DB::insert($this->tableName, $dataToInsert);

    return DB::insertId();
  }

  /**
   * @param array $data
   * @param int $id
   */
  public function update(array $data, int $id) {
    return DB::update(
      $this->tableName, 
      $data, 
      "id = %i",
      $id
    );
  }

}