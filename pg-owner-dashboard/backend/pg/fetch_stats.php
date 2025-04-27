<?php
session_start();
require '../db.php';
$user_id = $_SESSION['user_id'];

$total = $conn->query("SELECT COUNT(*) AS total FROM pg_listings WHERE user_id=$user_id")->fetch_assoc()['total'];
$views = rand(100, 500);  // Mocked
$likes = rand(10, 50);
$inquiries = rand(1, 20);

echo json_encode(compact('total', 'views', 'likes', 'inquiries'));
?>
