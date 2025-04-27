<?php
require '../db.php';
require '../pglife_db.php';

if (!isset($_GET['id'])) { echo "Missing ID"; exit; }
$id = (int)$_GET['id'];

// 1) Delete from pg_listings
$d1 = $conn->prepare("DELETE FROM pg_listings WHERE id=?");
$d1->bind_param("i",$id);
$ok1 = $d1->execute();

// 2) Delete from properties
$d2 = $pglife_conn->prepare("DELETE FROM properties WHERE listing_id=?");
$d2->bind_param("i",$id);
$ok2 = $d2->execute();

echo ($ok1 && $ok2) ? "success" : "error";
