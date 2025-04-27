<?php
session_start();
require '../db.php';
$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare("REPLACE INTO notifications (user_id, inquiry, rental_reminder, email_alert) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiii", $user_id, $data['inquiry'], $data['rental'], $data['email']);
echo $stmt->execute() ? "saved" : "error";
?>
