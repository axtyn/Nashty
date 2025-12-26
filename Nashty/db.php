<?php
// db.php
require_once 'config.php';

try {
    // Create connection string
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

    // Options
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Try to connect without DB name to create it if it doesn't exist
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        try {
            $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $pdo = new PDO($dsn_no_db, DB_USER, DB_PASS, $options);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
            $pdo->exec("USE `" . DB_NAME . "`");
        } catch (PDOException $ex) {
            die("Error creando base de datos: " . $ex->getMessage());
        }
    } else {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}
?>