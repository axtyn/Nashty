<?php
// test_list_v2.php
// Mock session inputs
$_GET['action'] = 'list'; // or $_POST
$_POST['action'] = 'list';
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Buffer output to capture what actions.php sends
ob_start();
include 'actions.php';
$output = ob_get_clean();

echo "--- START OUTPUT ---\n";
echo $output;
echo "\n--- END OUTPUT ---\n";
?>