<?php
/**
 * ============================================
 * SAZÓN DIGITAL - PUNTO DE ENTRADA PRINCIPAL
 * ============================================
 *
 * Este archivo es el "router" de la aplicación.
 * Todas las peticiones pasan por aquí (gracias al .htaccess).
 *
 * ¿Cómo funciona?
 * 1. El usuario visita: index.php?page=productos
 * 2. Este archivo lee el parámetro "page"
 * 3. Verifica si el usuario tiene permiso
 * 4. Carga la vista correspondiente
 *
 * Patrón: Front Controller - Un solo punto de entrada
 * que controla toda la navegación de la aplicación.
 */

// Cargar funciones auxiliares y middleware
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/middleware.php';

// Iniciar sesión PHP (necesaria para login y datos de usuario)
initSession();

// Leer la página solicitada (por defecto: login)
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Lista de páginas que NO requieren login
$publicPages = ['login'];

// Verificar autenticación para páginas protegidas
if (!in_array($page, $publicPages)) {
    requireLogin(); // Si no está logueado, redirige al login
}

// ============================================
// ENRUTAMIENTO - Cargar la vista según la página
// ============================================
// Cada "case" carga un archivo PHP diferente.
// Algunos cases también verifican roles específicos.
switch ($page) {

    // --- AUTENTICACIÓN ---
    case 'login':
        // Si ya está logueado, ir al dashboard
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

    // --- DASHBOARD ---
    case 'dashboard':
        require_once __DIR__ . '/views/dashboard.php';
        break;

    // --- PRODUCTOS ---
    case 'productos':
        require_once __DIR__ . '/views/productos/index.php';
        break;

    case 'producto-form':
        requireRole(['Administrador']); // Solo admin puede crear/editar
        require_once __DIR__ . '/views/productos/form.php';
        break;

    // --- PEDIDOS ---
    case 'pedidos':
        require_once __DIR__ . '/views/pedidos/index.php';
        break;

    case 'pedido-crear':
        requireRole(['Administrador', 'Mesero']); // Admin y mesero pueden crear
        require_once __DIR__ . '/views/pedidos/crear.php';
        break;

    case 'pedido-detalle':
        require_once __DIR__ . '/views/pedidos/detalle.php';
        break;

    // --- USUARIOS ---
    case 'usuarios':
        requireRole(['Administrador']); // Solo admin gestiona usuarios
        require_once __DIR__ . '/views/usuarios/index.php';
        break;

    case 'usuario-form':
        requireRole(['Administrador']);
        require_once __DIR__ . '/views/usuarios/form.php';
        break;

    // --- ESTADOS DE PEDIDO ---
    case 'estados':
        require_once __DIR__ . '/views/estados/index.php';
        break;

    // --- VENTAS ---
    case 'ventas':
        requireRole(['Administrador']); // Solo admin ve ventas
        require_once __DIR__ . '/views/ventas/index.php';
        break;

    // --- CAJERO ---
    case 'cajero':
        requireRole(['Administrador', 'Mesero']);
        require_once __DIR__ . '/views/cajero/index.php';
        break;

    // --- RESERVACIONES ---
    case 'reservaciones':
        require_once __DIR__ . '/views/reservaciones/index.php';
        break;

    // --- CONFIGURACIÓN ---
    case 'configuracion':
        requireRole(['Administrador']); // Solo admin configura horarios
        require_once __DIR__ . '/views/configuracion/horarios.php';
        break;

    // --- CLIENTES ---
    case 'clientes':
        requireRole(['Administrador', 'Mesero']);
        require_once __DIR__ . '/views/clientes/index.php';
        break;

    // --- PÁGINA NO ENCONTRADA ---
    default:
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
        echo '<a href="index.php?page=dashboard">Volver al Panel</a>';
        break;
}
