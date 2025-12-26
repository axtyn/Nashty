<?php
// login.php
require_once 'config.php';
require_once 'db_connect.php';

// Logout Logic
if (isset($_GET['logout'])) {
    // Unset all session values
    $_SESSION = array();

    // Get session parameters
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy session
    session_destroy();
    header("Location: login.php");
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepared statement for security
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Security: Regenerate Session ID to prevent fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Credenciales incorrectas.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            /* Fallback */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            /* Deep Purple/Blue */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        /* Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            color: white;
            position: relative;
        }

        .login-card h2 {
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }

        /* Inputs */
        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            color: #333;
        }

        .form-control::placeholder {
            color: #888;
        }

        .form-control:focus {
            background: #fff;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        .form-label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            font-size: 0.9em;
            opacity: 0.9;
        }

        /* Button */
        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            font-weight: 600;
            font-size: 1.1em;
            transition: 0.3s;
            backdrop-filter: blur(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateY(-2px);
        }

        .links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.9em;
            transition: 0.2s;
        }

        .links a:hover {
            color: white;
            text-decoration: underline;
        }

        .links {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="icon-container">
            <i class="fas fa-user"></i>
        </div>
        <h2>LOGIN</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2" role="alert"
                style="background: rgba(220, 53, 69, 0.8); border: none; color: white;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="text-start">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="text-start">
                <label class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <button type="submit" class="btn-login mt-3">Entrar</button>
        </form>

        <div class="links">
            <a href="forgot_password.php">¿Olvidaste tu contraseña?</a>
        </div>
    </div>

</body>

</html>