<?php
// forgot_password.php
require_once 'config.php';
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    // Mock logic
    $msg = "Si el correo existe, se han enviado las instrucciones. (Simulación)";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #5a4ad1 0%, #2d7ef7 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>

<body>
    <div class="card p-4 mx-3">
        <div class="text-center mb-4">
            <h4 class="fw-bold">Recuperar Acceso</h4>
            <p class="text-muted small">Ingresa tu email para restablecer</p>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success text-center"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Enviar Instrucciones</button>
                <a href="login.php" class="btn btn-light text-muted">Volver al Login</a>
            </div>
        </form>
    </div>
</body>

</html>