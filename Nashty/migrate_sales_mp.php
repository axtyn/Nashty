<?php
// migrate_sales_mp.php
require_once 'db_connect.php';

try {
    try {
        $pdo->exec("ALTER TABLE sales ADD COLUMN marketplace VARCHAR(50)");
        echo "Columna 'marketplace' agregada a tabla 'sales'.<br>";
    } catch (PDOException $e) {
        echo "Nota 'marketplace': " . $e->getMessage() . "<br>";
    }
    echo "Migración completada.";
} catch (PDOException $e) {
    die("Error Fatal: " . $e->getMessage());
}
?>