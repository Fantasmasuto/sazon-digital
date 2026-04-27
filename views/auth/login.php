<?php
/**
 * View: Login Page
 */
require_once __DIR__ . '/../../includes/helpers.php';

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $auth = new AuthController();
    $result = $auth->login();
    if ($result['success']) {
        redirect('index.php?page=dashboard');
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sazón Digital - Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <img src="assets/img/logo.png" alt="Sazón Digital" class="login-logo" onerror="this.style.display='none'">
            <h1>Sazón Digital</h1>
            <p class="subtitle">Sistema de Gestión de Restaurantes</p>

            <?php if ($error): ?>
                <div class="login-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login">
                <div class="form-group">
                    <input type="email" name="email" placeholder="Ingresa tu usuario" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Ingresa tu contraseña" required>
                </div>
                <div class="remember-group">
                    <input type="checkbox" name="remember_me" id="remember_me">
                    <label for="remember_me">Recordarme</label>
                </div>
                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </form>

            <p style="color: #666; margin-top: 20px; font-size: 0.75rem;">
                &copy; <?= date('Y') ?> Sazón Digital
            </p>
        </div>
    </div>
</body>
</html>
