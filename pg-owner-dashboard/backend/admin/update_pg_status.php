<?php
// backend/admin/update_pg_status.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

require '../db.php';          // dashboard DB
require '../pglife_db.php';  // pglife DB

if (!isset($_GET['id'])) {
  echo "Missing ID";
  exit;
}
$id = (int)$_GET['id'];

// Step 1: Mark PG as Verified
$u = $conn->prepare("UPDATE pg_listings SET status='Verified' WHERE id=?");
$u->bind_param("i", $id);
if (!$u->execute()) {
  echo "Error updating pg_listings: " . $u->error;
  exit;
}

// Step 2: Fetch the PG listing
$f = $conn->prepare("
  SELECT name, city, area, address,
         description, images, type, rent,
         rooms_single, rooms_double, rooms_triple,
         video_link, amenities
    FROM pg_listings
   WHERE id=?
");
$f->bind_param("i", $id);
$f->execute();
$row = $f->get_result()->fetch_assoc();
if (!$row) {
  echo "Listing not found";
  exit;
}

// Extract first image for 'file' column
$file = "";
if (!empty($row['images'])) {
  $parts = explode(",", $row['images']);
  $file = trim($parts[0]);
}

// Use PG type as gender
$gender = $row['type'];
$city   = trim($row['city']);
$city_id = 0;

// Step 3: Check if city exists, insert if not
$c = $pglife_conn->prepare("SELECT id FROM cities WHERE name = ?");
$c->bind_param("s", $city);
$c->execute();
$cres = $c->get_result()->fetch_assoc();
$c->close();

if ($cres) {
  $city_id = intval($cres['id']);
} else {
  // Insert new city
  $iCity = $pglife_conn->prepare("INSERT INTO cities (name) VALUES (?)");
  $iCity->bind_param("s", $city);
  if (!$iCity->execute()) {
    echo "Insert city failed: " . $iCity->error;
    exit;
  }
  $city_id = $iCity->insert_id;
  $iCity->close();
}

// Step 4: Insert into properties table
$ins = $pglife_conn->prepare("
  INSERT INTO properties (
    listing_id, city_id, name, address, description,
    file, gender, rent,
    rating_clean, rating_food, rating_safety,
    video_link, amenities
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, ?, ?)
");
if (!$ins) {
  echo "Prepare INSERT failed: " . $pglife_conn->error;
  exit;
}

// Bind 10 parameters (matching 10 placeholders)
$ins->bind_param(
  "iisssssiss",
  $id,
  $city_id,
  $row['name'],
  $row['address'],
  $row['description'],
  $file,
  $gender,
  $row['rent'],
  $row['video_link'],
  $row['amenities']
);

if (!$ins->execute()) {
  echo "Execute INSERT failed: " . $ins->error;
  exit;
}

echo "success";
