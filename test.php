<?php

if (isset($_GET["feed-test"])) {
  $count = 10000;

  for ($i=1;$i<=$count;$i++) {
    DB::insert('ucm_skladky', [
      'nazov' => "zatazovy_test_record_{$i}",
      'okres' => "zatazovy_test_record_{$i}",
      'obec' => "zatazovy_test_record_{$i}",
      'rok_zacatia' => Date('Y-m-d'),
      'typ' => 1,
      'lat' => 0,
      'lng' => 0
    ]);
  }

  exit("FEED SUCCESS");
}