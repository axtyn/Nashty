<?php
// migrate_sales.php
require_once 'db_connect.php';

try {
    // Add last_sold_at
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN last_sold_at TIMESTAMP NULL");
        echo "Columna 'last_sold_at' agregada.<br>";
    } catch (PDOException $e) {
        echo "Nota 'last_sold_at': " . $e->getMessage() . "<br>";
    }

    echo "Migración de ventas completada.";

} catch (PDOException $e) {
    die("Error Fatal: " . $e->getMessage());
}
?>