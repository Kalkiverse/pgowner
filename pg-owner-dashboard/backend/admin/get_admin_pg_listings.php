<?php
require '../db.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT * FROM pg_listings ORDER BY id DESC");
$listings = [];

while ($row = $result->fetch_assoc()) {
    $row['rooms'] = 
        intval($row['rooms_single'])
      + intval($row['rooms_double'])
      + intval($row['rooms_triple']);
    $listings[] = $row;
}

echo json_encode($listings);
