<?php
// test_list.php
$_POST['action'] = 'list';
// Mock session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

require 'actions.php';
?>