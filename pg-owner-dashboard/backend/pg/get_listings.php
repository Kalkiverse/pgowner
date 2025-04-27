<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$query = "SELECT id, name, city, area, type, rooms_single, rooms_double, rooms_triple, rent, status 
          FROM pg_listings WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$listings = [];
while ($row = $result->fetch_assoc()) {
    $row['location'] = $row['city'] . ", " . $row['area'];
    $rooms = 0;
    if ($row['rooms_single']) $rooms += $row['rooms_single'];
    if ($row['rooms_double']) $rooms += $row['rooms_double'];
    if ($row['rooms_triple']) $rooms += $row['rooms_triple'];
    $row['rooms'] = $rooms;
    $listings[] = $row;
}
$stmt->close();
echo json_encode($listings);
