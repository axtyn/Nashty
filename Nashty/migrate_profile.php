<?php
// migrate_profile.php
require_once 'db_connect.php';

try {
    // Attempt to add columns if they don't exist
    // Simple way: Try ALTER and catch exception if duplicate column name

    // Add phone
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20)");
        echo "Columna 'phone' agregada.<br>";
    } catch (PDOException $e) {
        // Warning: this generic catch might hide other errors, but usually it's "Duplicate column"
        echo "Nota 'phone': " . $e->getMessage() . "<br>";
    }

    // Add photo
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN photo VARCHAR(255)");
        echo "Columna 'photo' agregada.<br>";
    } catch (PDOException $e) {
        echo "Nota 'photo': " . $e->getMessage() . "<br>";
    }

    echo "Migración de perfil completada.";

} catch (PDOException $e) {
    die("Error Fatal: " . $e->getMessage());
}
?>