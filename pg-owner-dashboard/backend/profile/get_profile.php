<?php
session_start();
require '../db.php';
$user_id = $_SESSION['user_id'];

$res = $conn->query("SELECT * FROM profile WHERE user_id=$user_id");
echo json_encode($res->fetch_assoc());
?>
