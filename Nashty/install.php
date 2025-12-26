<?php
session_start();
$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'install') {
        // Collect Data
        $db_host = $_POST['db_host'] ?? 'localhost';
        $db_name = $_POST['db_name'] ?? '';
        $db_user = $_POST['db_user'] ?? '';
        $db_pass = $_POST['db_pass'] ?? '';

        $admin_user = $_POST['admin_user'] ?? 'admin';
        $admin_pass = $_POST['admin_pass'] ?? '';
        $admin_email = $_POST['admin_email'] ?? 'admin@example.com';

        // 1. Verify Verification
        try {
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$db_name`");

            // 2. Run SQL Schema
            if (file_exists('database.sql')) {
                $sql = file_get_contents('database.sql');
                $pdo->exec($sql);
            } else {
                throw new Exception("El archivo database.sql no se encuentra.");
            }

            // 3. Create Admin
            $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            // Check if admin exists to avoid dupes if re-installing on existing DB
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$admin_user]);
            if (!$stmt->fetch()) {
                $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'admin')")
                    ->execute([$admin_user, $hash, $admin_email]);
            } else {
                // Update existing admin
                $pdo->prepare("UPDATE users SET password = ?, email = ?, role = 'admin' WHERE username = ?")
                    ->execute([$hash, $admin_email, $admin_user]);
            }

            // 4. Write Config File
            $configContent = "<?php\n";
            $configContent .= "session_start();\n\n";
            $configContent .= "define('DB_HOST', '$db_host');\n";
            $configContent .= "define('DB_NAME', '$db_name');\n";
            $configContent .= "define('DB_USER', '$db_user');\n";
            $configContent .= "define('DB_PASS', '$db_pass');\n\n";
            $configContent .= "define('APP_NAME', 'NASHTY');\n\n";
            $configContent .= "// Helper to format currency\n";
            $configContent .= "function formatCurrency(\$amount) {\n";
            $configContent .= "    return '$' . number_format(\$amount, 2);\n";
            $configContent .= "}\n";

            if (file_put_contents('config.php', $configContent)) {
                header("Location: install.php?step=complete");
                exit;
            } else {
                throw new Exception("No se pudo escribir el archivo config.php.");
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación NASHTY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            color: #fff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .install-card {
            background: #1e293b;
            padding: 2rem;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .form-control {
            background: #334155;
            border: 1px solid #475569;
            color: #fff;
        }

        .form-control:focus {
            background: #334155;
            color: #fff;
            border-color: #5a4ad1;
            box-shadow: 0 0 0 0.2rem rgba(90, 74, 209, 0.25);
        }

        .btn-primary {
            background-color: #5a4ad1;
            border: none;
        }

        .btn-primary:hover {
            background-color: #4c3db3;
        }
    </style>
</head>

<body>

    <div class="install-card">
        <div class="text-center mb-4">
            <h2 class="fw-bold">NASHTY</h2>
            <p class="text-white-50">Instalador Automático</p>
        </div>

        <?php if ($step == 'complete'): ?>
            <div class="text-center">
                <div class="mb-4 text-success" style="font-size: 3rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor"
                        class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path
                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                    </svg>
                </div>
                <h4 class="mb-3">¡Instalación Exitosa!</h4>
                <p class="text-white-50">NASHTY ha sido configurado correctamente.</p>
                <a href="index.php" class="btn btn-primary w-100 mt-3">Ir al Panel de Control</a>
                <div class="mt-3 small text-warning">
                    Nota: Por seguridad, elimina el archivo <code>install.php</code> después de entrar.
                </div>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="install">

                <h6 class="text-uppercase text-white-50 mb-3 small fw-bold ls-1 border-bottom border-secondary pb-2">
                    Configuración Base de Datos</h6>
                <div class="mb-3">
                    <label class="form-label">Host (Servidor)</label>
                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre Base de Datos</label>
                    <input type="text" name="db_name" class="form-control" placeholder="ej. nashty_db" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label">Usuario BD</label>
                        <input type="text" name="db_user" class="form-control" placeholder="root" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Contraseña BD</label>
                        <input type="text" name="db_pass" class="form-control" placeholder="">
                    </div>
                </div>

                <h6 class="text-uppercase text-white-50 mb-3 mt-4 small fw-bold ls-1 border-bottom border-secondary pb-2">
                    Cuenta Administrador</h6>
                <div class="mb-3">
                    <label class="form-label">Usuario Admin</label>
                    <input type="text" name="admin_user" class="form-control" placeholder="admin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña Admin</label>
                    <input type="password" name="admin_pass" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Admin</label>
                    <input type="email" name="admin_email" class="form-control" placeholder="admin@nashty.com">
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3 py-2 fw-bold">Instalar NASHTY</button>
            </form>
        <?php endif; ?>
    </div>

</body>

</html>