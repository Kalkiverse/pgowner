<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);  // TEMPORARY for debugging
error_reporting(E_ALL);

require_once('../db.php');
require_once('PHPMailer/Exception.php');
require_once('PHPMailer/PHPMailer.php');
require_once('PHPMailer/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get email from the request
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required.']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'No user with this email.']);
    exit;
}

// Generate reset token and expiry time
$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+20 hour'));

// Store token and expiry
$update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
$update->bind_param("sss", $token, $expiry, $email);
$update->execute();

// Send the email with reset link
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // ✅ Correct SMTP Host (example: Gmail)
    $mail->SMTPAuth = true;
    $mail->Username = 'team.pgowner@gmail.com'; // ✅ Your Email
    $mail->Password = 'zvtsvcssvsjsgamh';  // ✅ Your Email App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('team.pgowner@gmail.com', 'PG Owner');
    $mail->addAddress($email);

    $resetLink = "http://localhost/pg-owner-dashboard/reset-password.html?token=" . $token;

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body    = "Click on the link below to reset your password: <br><a href='$resetLink'>$resetLink</a>";

    $mail->send();
    echo json_encode(['message' => 'Reset link sent to your email.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to send email.', 'details' => $mail->ErrorInfo]);
}
?>