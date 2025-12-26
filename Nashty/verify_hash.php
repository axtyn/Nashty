<?php
require_once 'db_connect.php';
$username = 'admin';
$password = 'admin123';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    if (password_verify($password, $user['password'])) {
        echo "SUCCESS: Password 'admin123' matches the hash in DB.";
    } else {
        echo "FAIL: Password 'admin123' does NOT match the hash.";
    }
} else {
    echo "FAIL: User 'admin' not found.";
}
?>