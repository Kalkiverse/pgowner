<?php
// backend/profile/get_profile_data.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// DB connection
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'owner';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Join profile and users tables; now we also select the 'name' column from users.
$sql = "
SELECT p.user_id,
       p.first_name,
       p.last_name,
       p.phone,
       p.city,
       p.state,
       p.address,
       p.about,
       p.business_name,
       p.business_type,
       p.gst_number,
       p.pan_number,
       p.business_address,
       u.email,
       u.name
  FROM profile p
  JOIN users u ON u.id = p.user_id
 WHERE p.user_id = ?
 LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

if ($row) {
    echo json_encode([
        'success' => true,
        'profile' => $row
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'No profile data found for this user.'
    ]);
}
?>
