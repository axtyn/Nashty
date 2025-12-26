<?php
// config.php
session_start();

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventory_sys');
define('DB_USER', 'root');
define('DB_PASS', '');

define('APP_NAME', 'NASHTY');

// Helper to format currency
function formatCurrency($amount)
{
    return '$' . number_format($amount, 2);
}

