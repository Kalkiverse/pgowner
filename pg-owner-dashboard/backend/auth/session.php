<?php
session_start();
echo isset($_SESSION['user_id']) ? "valid" : "invalid";
?>
