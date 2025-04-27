<?php
// backend/profile/update_business_info.php

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

$required_fields = ['business_name','business_type','gst_number','pan_number','business_address'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success'=> false, 'message' => "Field $field is missing"]);
        exit;
    }
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

$stmt = $conn->prepare("
    UPDATE profile
       SET business_name    = ?, 
           business_type    = ?, 
           gst_number       = ?, 
           pan_number       = ?, 
           business_address = ?
     WHERE user_id = ?
");
$stmt->bind_param(
    'sssssi',
    $input['business_name'],
    $input['business_type'],
    $input['gst_number'],
    $input['pan_number'],
    $input['business_address'],
    $user_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Business information updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update business information']);
}

$stmt->close();
$conn->close();
?>
