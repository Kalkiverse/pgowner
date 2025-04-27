<?php 
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
  echo 'unauthorized';
  exit;
}

$user_id = (int) $_SESSION['user_id'];

// Use $_POST instead of json_decode since we are using FormData now
$name           = $_POST['name'];
$type           = $_POST['type'];
$description    = $_POST['description'];
$city           = $_POST['city'];
$area           = $_POST['area'];
$address        = $_POST['address'];
$rent           = (int)$_POST['rent'];
$deposit        = (int)$_POST['deposit'];
$discount       = (int)$_POST['discount'];
$amenities      = $_POST['amenities'];
$rooms_single   = (int)$_POST['rooms_single'];
$rooms_double   = (int)$_POST['rooms_double'];
$rooms_triple   = (int)$_POST['rooms_triple'];
$rules          = $_POST['rules'];
$notice_period  = (int)$_POST['notice_period'];
$status         = $_POST['status'];
$contact_person = $_POST['contact_person'];
$contact_phone  = $_POST['contact_phone'];
$video_link     = $_POST['video_link'];

// Handle image uploads
$image_paths = [];
$upload_dir = "../uploads/";

if (!file_exists($upload_dir)) {
  mkdir($upload_dir, 0777, true);
}

if (!empty($_FILES['images']['name'][0])) {
  foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
    $filename = time() . "_" . basename($_FILES['images']['name'][$key]);
    $target_path = $upload_dir . $filename;

    if (move_uploaded_file($tmp_name, $target_path)) {
      $image_paths[] = "uploads/" . $filename;
    }
  }
}

$images_str = implode(",", $image_paths); // comma-separated image paths

// Add 'images' column to your DB before running this
$stmt = $conn->prepare("
  INSERT INTO pg_listings (
    user_id, name, type, description, city, area, address,
    rent, deposit, discount, amenities,
    rooms_single, rooms_double, rooms_triple,
    rules, notice_period, status,
    contact_person, contact_phone, video_link, images
  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
  echo 'prepare failed: ' . $conn->error;
  exit;
}

// 21 fields → i (int), s (string)
$stmt->bind_param(
  "issssssiiissiiissssss", // ← NOW IT MATCHES 21 VALUES (added one more 's')
  $user_id,
  $name,
  $type,
  $description,
  $city,
  $area,
  $address,
  $rent,
  $deposit,
  $discount,
  $amenities,
  $rooms_single,
  $rooms_double,
  $rooms_triple,
  $rules,
  $notice_period,
  $status,
  $contact_person,
  $contact_phone,
  $video_link,
  $images_str
);

if ($stmt->execute()) {
  echo 'success';
} else {
  echo 'error: ' . $stmt->error;
}

