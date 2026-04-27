<?php
/**
 * ============================================
 * MIDDLEWARE DE AUTENTICACIÓN Y AUTORIZACIÓN
 * ============================================
 *
 * ¿Qué es un Middleware?
 * Es código que se ejecuta ANTES de procesar una solicitud.
 * Funciona como un "guardia de seguridad" que verifica permisos.
 *
 * Flujo: Usuario hace petición -> Middleware verifica -> Si pasa, continúa
 *
 * Hay dos tipos de middleware aquí:
 * 1. Para VISTAS (HTML): redirige al login si no está autenticado
 * 2. Para API (JSON): retorna error 401/403 si no está autenticado
 */

require_once __DIR__ . '/helpers.php';

/**
 * Requiere que el usuario esté logueado.
 * Si no lo está, intenta usar la cookie "Remember Me".
 * Si no funciona, redirige al formulario de login.
 *
 * Se usa en index.php antes de cargar las vistas protegidas.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Intentar auto-login con cookie "Remember Me"
        if (isset($_COOKIE['remember_token'])) {
            require_once __DIR__ . '/../services/AuthService.php';
            $authService = new AuthService();
            if ($authService->loginWithToken($_COOKIE['remember_token'])) {
                return; // Login exitoso con token
            }
        }
        // No hay sesión ni cookie válida -> ir al login
        redirect('index.php?page=login');
    }
}

/**
 * Requiere que el usuario tenga un rol específico.
 * Primero verifica que esté logueado, luego verifica el rol.
 *
 * Acepta un string o un array de roles permitidos.
 * Ejemplo: requireRole(['Administrador', 'Mesero'])
 *
 * @param string|array $roles Rol o roles permitidos
 */
function requireRole($roles) {
    requireLogin();
    $user = getCurrentUser();

    // Convertir a array si se recibió un string
    if (!is_array($roles)) {
        $roles = [$roles];
    }

    // Verificar si el rol del usuario está en la lista de roles permitidos
    $userRole = strtolower($user['rol']);
    foreach ($roles as $role) {
        if (strtolower($role) === $userRole) {
            return; // Tiene permiso, continuar
        }
    }

    // No tiene permiso -> mostrar error 403 (Forbidden)
    http_response_code(403);
    echo '<h1>Acceso Denegado</h1><p>No tienes permisos para acceder a esta sección.</p>';
    echo '<a href="index.php">Volver al inicio</a>';
    exit;
}

/**
 * Regla de negocio: Requiere que el restaurante esté abierto.
 * Se usa antes de crear pedidos, ventas o reservaciones.
 * Si está cerrado, redirige al dashboard con un mensaje de advertencia.
 */
function requireRestaurantOpen() {
    if (!isRestaurantOpen()) {
        setFlash('warning', 'El restaurante está cerrado en este momento. No se puede realizar esta operación.');
        redirect('index.php?page=dashboard');
    }
}

/**
 * Versión API del middleware de login.
 * En lugar de redirigir (que no funciona con AJAX),
 * retorna un error JSON con código 401 (Unauthorized).
 */
function requireApiLogin() {
    initSession();
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'No autenticado'], 401);
    }
}

/**
 * Versión API del middleware de roles.
 * Retorna error JSON 403 (Forbidden) si el rol no es correcto.
 *
 * @param string|array $roles Rol o roles permitidos
 */
function requireApiRole($roles) {
    requireApiLogin();
    $user = getCurrentUser();
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    $userRole = strtolower($user['rol']);
    foreach ($roles as $role) {
        if (strtolower($role) === $userRole) {
            return;
        }
    }
    jsonResponse(['error' => 'No autorizado'], 403);
}
