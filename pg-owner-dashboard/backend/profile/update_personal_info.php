<?php
// backend/profile/update_personal_info.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}
$user_id = $_SESSION['user_id'];

// Get JSON data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

// Validate required fields
$required_fields = ['first_name','last_name','email','phone','city','state','address','about'];
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        echo json_encode(['success'=> false, 'message' => "Field $field is missing"]);
        exit;
    }
}

// Connect to DB
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'owner';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $conn->connect_error]);
    exit;
}

// ---- 1) Upsert into the "profile" table ----
// Check if a profile record exists for this user
$stmtCheck = $conn->prepare("SELECT user_id FROM profile WHERE user_id = ?");
$stmtCheck->bind_param('i', $user_id);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    // A row exists, so update it
    $stmtCheck->close();
    $stmtProfile = $conn->prepare("
        UPDATE profile
           SET first_name = ?, 
               last_name  = ?, 
               phone      = ?, 
               city       = ?, 
               state      = ?,
               address    = ?, 
               about      = ?
         WHERE user_id = ?
    ");
    $stmtProfile->bind_param(
        'sssssssi',
        $input['first_name'],
        $input['last_name'],
        $input['phone'],
        $input['city'],
        $input['state'],
        $input['address'],
        $input['about'],
        $user_id
    );
    $profileUpdated = $stmtProfile->execute();
    $stmtProfile->close();
} else {
    // No row exists, so insert a new one
    $stmtCheck->close();
    $stmtProfile = $conn->prepare("
        INSERT INTO profile (user_id, first_name, last_name, phone, city, state, address, about)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtProfile->bind_param(
        'isssssss',
        $user_id,
        $input['first_name'],
        $input['last_name'],
        $input['phone'],
        $input['city'],
        $input['state'],
        $input['address'],
        $input['about']
    );
    $profileUpdated = $stmtProfile->execute();
    $stmtProfile->close();
}

// ---- 2) Update the "users" table for email and combined name ----
// Combine first_name and last_name into the 'name' column.
$fullName = trim($input['first_name'] . ' ' . $input['last_name']);
$stmtUser = $conn->prepare("
    UPDATE users
       SET name  = ?, 
           email = ?
     WHERE id    = ?
");
$stmtUser->bind_param('ssi', $fullName, $input['email'], $user_id);
$userUpdated = $stmtUser->execute();
$stmtUser->close();

$conn->close();

if ($profileUpdated && $userUpdated) {
    echo json_encode(['success' => true, 'message' => 'Personal info updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update personal info']);
}
?>