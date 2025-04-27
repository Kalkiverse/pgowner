<?php
session_start();
require '../db.php';
$data = json_decode(file_get_contents("php://input"));

$email = $data->email;
$password = $data->password;

$result = $conn->query("SELECT * FROM users WHERE email = '$email'");
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
  $_SESSION['user_id'] = $user['id'];
  echo "success";
} else {
  echo "invalid";
}
?>
