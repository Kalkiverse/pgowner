<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);  // TEMPORARY for debugging
error_reporting(E_ALL);

require_once('../db.php');

// Get token and new password from the request
$data = json_decode(file_get_contents("php://input"), true);
$token = $data['token'] ?? '';
$newPassword = $data['password'] ?? '';

if (empty($token) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['error' => 'Token and new password are required.']);
    exit;
}

// Check if the token exists and is not expired
$stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Invalid or expired token.']);
    exit;
}

$row = $result->fetch_assoc();
$userId = $row['id'];

// Hash the new password
$hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

// Update the password and clear the token
$update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
$update->bind_param("si", $hashedPassword, $userId);

if ($update->execute()) {
    echo json_encode(['message' => 'Password updated successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update password. Please try again.']);
}
?>
