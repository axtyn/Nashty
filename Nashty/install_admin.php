<?php
// install_admin.php
require_once 'includes/db_connect.php';

$email = 'admin@tienda.com';
$password = 'admin123'; // Contraseña fácil para empezar
$hash = password_hash($password, PASSWORD_DEFAULT);
$username = 'Super Admin';
$role = 'admin';

try {
    // Verificar si ya existe
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() > 0) {
        // Si existe, actualizamos la contraseña a admin123
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = ? WHERE email = ?");
        $stmt->execute([$hash, $role, $email]);
        echo "<h1>¡Usuario Actualizado!</h1>";
    } else {
        // Si no existe, lo creamos
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $hash, $role]);
        echo "<h1>¡Usuario Creado!</h1>";
    }
    
    echo "<p>User: <strong>$email</strong></p>";
    echo "<p>Pass: <strong>$password</strong></p>";
    echo "<br><a href='index.php'>Ir al Login</a>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>