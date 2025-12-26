<?php
require_once 'db_connect.php';
$stmt = $pdo->query("SELECT id, username, password FROM users");
while ($row = $stmt->fetch()) {
    echo "User: " . $row['username'] . " | Hash: " . substr($row['password'], 0, 10) . "...\n";
}
?>