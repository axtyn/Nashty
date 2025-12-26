<?php
require_once 'db_connect.php';
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
echo "Total Products: " . $stmt->fetchColumn();
?>