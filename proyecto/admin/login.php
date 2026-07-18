<?php
require_once '../includes/config.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($usuario) || empty($password)) {
        $error = 'Por favor, ingrese usuario y contraseña.';
    } else {
        $stmt = $conexion->prepare("SELECT id, usuario, password, nombre FROM admin WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Contraseña incorrecta.';
            }
        } else {
            $error = 'El usuario no existe.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Compu Magic</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-body">
    <div class="login-split-left">
        <div class="login-brand">
            <span class="badge-icono"><i class="fas fa-laptop-code"></i></span>
            Compu Magic
        </div>
        <h1>Gestiona tu tienda desde un solo panel.</h1>
        <p>Productos, categorías, ventas y stock — todo lo que necesitas para llevar el negocio, en un mismo lugar.</p>
    </div>
    <div class="login-split-right">
        <div class="login-container">
            <h2>Iniciar sesión</h2>
            <p class="login-subtitulo">Ingresa tus credenciales de administrador</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="usuario" required autofocus>
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
