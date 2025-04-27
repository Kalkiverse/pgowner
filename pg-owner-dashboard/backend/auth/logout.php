<?php
// backend/auth/logout.php

session_start();
session_unset();  // remove all session variables
session_destroy(); // destroy the session

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully.'
]);
exit;
