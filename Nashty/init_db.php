<?php
// init_db.php
require_once 'config.php';

try {
    // Connect without DB selected to create it
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents('database.sql');

    // Execute SQL script (basic splitter for statements)
    // Note: PDO exec doesn't handle multiple statements well in one go sometimes, so we split
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    echo "Base de datos actualizada correctamente con esquema experto.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>