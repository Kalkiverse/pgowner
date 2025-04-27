<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
  echo 'unauthorized';
  exit;
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$stmt = $conn->prepare("REPLACE INTO profile (
  user_id, first_name, last_name, email, phone,
  city, state, address, about,
  business_name, business_type, gst_number, pan_number, business_address
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
  "isssssssssssss",
  $user_id,
  $data['first_name'],
  $data['last_name'],
  $data['email'],
  $data['phone'],
  $data['city'],
  $data['state'],
  $data['address'],
  $data['about'],
  $data['business_name'],
  $data['business_type'],
  $data['gst_number'],
  $data['pan_number'],
  $data['business_address']
);

if ($stmt->execute()) {
  echo 'updated';
} else {
  echo 'error';
}
?>
