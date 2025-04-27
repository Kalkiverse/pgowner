<?php
// pglife application DB
$host2   = "127.0.0.1";
$user2   = "root";
$pass2   = "";
$db2     = "pglife";  // your pglife DB name

$pglife_conn = new mysqli($host2, $user2, $pass2, $db2);
if ($pglife_conn->connect_error) {
  die("PGLife DB connection failed: " . $pglife_conn->connect_error);
}
