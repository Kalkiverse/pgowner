<?php
session_start();
require '../db.php';
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $_SESSION['user_id'];

$current = $data['current'];
$new = $data['new'];

$res = $conn->query("SELECT password FROM users WHERE id=$user_id");
$row = $res->fetch_assoc();

if (password_verify($current, $row['password'])) {
  $new_hash = password_hash($new, PASSWORD_BCRYPT);
  $conn->query("UPDATE users SET password='$new_hash' WHERE id=$user_id");
  echo "success";
} else {
  echo "incorrect";
}
?>
