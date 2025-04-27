<?php
// backend/profile/update_security.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['current_password']) || empty($input['new_password'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete data received']);
    exit;
}

$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'owner';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// 1) Fetch current hashed password from "users"
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($hashedPassword);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'User record not found']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// 2) Verify old password
if (!password_verify($input['current_password'], $hashedPassword)) {
    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
    $conn->close();
    exit;
}

// 3) Update with the new hashed password
$newHashedPassword = password_hash($input['new_password'], PASSWORD_DEFAULT);
$stmtUpdate = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmtUpdate->bind_param('si', $newHashedPassword, $user_id);

if ($stmtUpdate->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
}

$stmtUpdate->close();
$conn->close();
?>
