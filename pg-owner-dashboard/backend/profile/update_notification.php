<?php
// backend/profile/update_notification.php

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
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$inquiry         = isset($input['inquiry'])         ? (int)$input['inquiry']         : 0;
$rental_reminder = isset($input['rental_reminder']) ? (int)$input['rental_reminder'] : 0;
$email_alert     = isset($input['email_alert'])     ? (int)$input['email_alert']     : 0;

$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'owner';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if notifications row already exists
$stmtCheck = $conn->prepare("SELECT user_id FROM notifications WHERE user_id = ?");
$stmtCheck->bind_param('i', $user_id);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    // Row exists -> update
    $stmtCheck->close();
    $stmtUpdate = $conn->prepare("
        UPDATE notifications
           SET inquiry         = ?, 
               rental_reminder = ?, 
               email_alert     = ?
         WHERE user_id         = ?
    ");
    $stmtUpdate->bind_param('iiii', $inquiry, $rental_reminder, $email_alert, $user_id);
    if ($stmtUpdate->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification settings updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update notification settings']);
    }
    $stmtUpdate->close();
} else {
    // Row doesn’t exist -> insert
    $stmtCheck->close();
    $stmtInsert = $conn->prepare("
        INSERT INTO notifications (user_id, inquiry, rental_reminder, email_alert)
        VALUES (?, ?, ?, ?)
    ");
    $stmtInsert->bind_param('iiii', $user_id, $inquiry, $rental_reminder, $email_alert);
    if ($stmtInsert->execute()) {
        echo json_encode(['success' => true, 'message' => 'Notification settings inserted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to insert notification settings']);
    }
    $stmtInsert->close();
}

$conn->close();
?>
