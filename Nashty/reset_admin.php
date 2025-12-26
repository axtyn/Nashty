<?php
// reset_admin.php
require_once 'db_connect.php';

$pass = 'admin123';
$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
        $update->execute([$hash]);
        echo "Admin password reset to: $pass";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')");
        $insert->execute([$hash]);
        echo "Admin user created with password: $pass";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>