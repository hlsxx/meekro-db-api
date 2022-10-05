<?php

define("ITEMS_PER_PAGE", 6);

function getOffset() {
  return ($_GET["pagination"] - 1) * ITEMS_PER_PAGE;
}

function getPaginationData() {
  echo json_encode(
    DB::query("
      SELECT 
        * 
      FROM ucm_skladky
      LIMIT %d, %d
    ", 
      getOffset(),
      ITEMS_PER_PAGE
    )
  );
}