<?php
// backend/user/get_user_profile.php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error'=>'unauthorized']);
  exit;
}
$uid = (int)$_SESSION['user_id'];

// 1) Try to get full_name from profile table
$p = $conn->prepare("SELECT full_name FROM profile WHERE user_id = ?");
$p->bind_param("i", $uid);
$p->execute();
$pr = $p->get_result()->fetch_assoc();

// 2) Fallback to users.full_name if none in profile
if (!empty($pr['full_name'])) {
  $name = $pr['full_name'];
} else {
  $u = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
  $u->bind_param("i", $uid);
  $u->execute();
  $ur = $u->get_result()->fetch_assoc();
  $name = $ur['full_name'] ?? '';
}

// 3) Return JSON
echo json_encode(['full_name' => $name]);
