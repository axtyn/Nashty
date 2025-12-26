<?php
// test_profile.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connect.php';

echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not Set') . "<br>";

if (!isset($_SESSION['user_id'])) {
    die("No active session for testing.");
}

try {
    $s = $pdo->prepare("SELECT username, email, phone, role, photo FROM users WHERE id = ?");
    $s->execute([$_SESSION['user_id']]);
    $data = $s->fetch();
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>