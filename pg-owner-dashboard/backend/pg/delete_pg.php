<?php
session_start();
require '../db.php';  // Adjust path as needed

if (!isset($_SESSION['user_id'])) {
    echo "unauthorized";
    exit;
}

$user_id = (int)$_SESSION['user_id'];

if (!isset($_GET['id'])) {
    echo "missing id";
    exit;
}

$pg_id = (int)$_GET['id'];

// Verify that the listing belongs to the user
$stmt = $conn->prepare("SELECT id FROM pg_listings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $pg_id, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo "not found";
    exit;
}
$stmt->close();

// Delete the listing
$stmt = $conn->prepare("DELETE FROM pg_listings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $pg_id, $user_id);
if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: " . $stmt->error;
}
$stmt->close();
