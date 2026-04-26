<?php
/**
 * Sazón Digital - Main Entry Point / Router
 * Simple page-based routing for XAMPP
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/middleware.php';

initSession();

// Get the requested page
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Pages that don't require login
$publicPages = ['login'];

// Check authentication for protected pages
if (!in_array($page, $publicPages)) {
    requireLogin();
}

// Route to the appropriate view
switch ($page) {
    case 'login':
        if (isLoggedIn()) {
            redirect('index.php?page=dashboard');
        }
        require_once __DIR__ . '/views/auth/login.php';
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        $auth = new AuthController();
        $auth->logout();
        break;

    case 'dashboard':
        require_once __DIR__ . '/views/dashboard.php';
        break;

    case 'productos':
        require_once __DIR__ . '/views/productos/index.php';
        break;

    case 'producto-form':
        requireRole(['Administrador']);
        require_once __DIR__ . '/views/productos/form.php';
        break;

    case 'pedidos':
        require_once __DIR__ . '/views/pedidos/index.php';
        break;

    case 'pedido-crear':
        requireRole(['Administrador', 'Mesero']);
        require_once __DIR__ . '/views/pedidos/crear.php';
        break;

    case 'pedido-detalle':
        require_once __DIR__ . '/views/pedidos/detalle.php';
        break;

    case 'usuarios':
        requireRole(['Administrador']);
        require_once __DIR__ . '/views/usuarios/index.php';
        break;

    case 'usuario-form':
        requireRole(['Administrador']);
        require_once __DIR__ . '/views/usuarios/form.php';
        break;

    case 'estados':
        require_once __DIR__ . '/views/estados/index.php';
        break;

    case 'ventas':
        requireRole(['Administrador']);
        require_once __DIR__ . '/views/ventas/index.php';
        break;

    case 'reservaciones':
        require_once __DIR__ . '/views/reservaciones/index.php';
        break;

    case 'configuracion':
        requireRole(['Administrador']);
        require_once __DIR__ . '/views/configuracion/horarios.php';
        break;

    case 'clientes':
        requireRole(['Administrador', 'Mesero']);
        require_once __DIR__ . '/views/clientes/index.php';
        break;

    default:
        echo '<h1>404 - Página no encontrada</h1>';
        echo '<a href="index.php?page=dashboard">Volver al inicio</a>';
        break;
}
