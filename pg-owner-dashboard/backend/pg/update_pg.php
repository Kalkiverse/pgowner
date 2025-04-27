<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo "invalid input";
    exit;
}

$id = (int)$data['id'];
$name = $data['name'];
$city = $data['city'];
$area = $data['area'];
$type = $data['type'];
$rooms = (int)$data['rooms'];
$rent = (float)$data['rent'];
$status = $data['status'];

$stmt = $conn->prepare("UPDATE pg_listings SET name = ?, city = ?, area = ?, type = ?, rooms_single = ?, rent = ?, status = ? WHERE id = ? AND user_id = ?");
if (!$stmt) {
    echo "prepare failed: " . $conn->error;
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$stmt->bind_param("ssssidsii", $name, $city, $area, $type, $rooms, $rent, $status, $id, $user_id);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}
$stmt->close();
