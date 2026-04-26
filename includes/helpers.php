<?php
/**
 * Helper functions used across the application
 */

// Start session if not already started
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    initSession();
    return isset($_SESSION['usuario_id']);
}

// Get current user data from session
function getCurrentUser() {
    initSession();
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'email'  => $_SESSION['usuario_email'],
        'rol'    => $_SESSION['usuario_rol'],
        'rol_id' => $_SESSION['usuario_rol_id']
    ];
}

// Check if current user has a specific role
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) return false;
    return strtolower($user['rol']) === strtolower($role);
}

// Redirect to a URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Send JSON response (for API endpoints)
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get base URL of the application
function baseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    // Remove trailing slash
    $path = rtrim($path, '/');
    return "$protocol://$host$path";
}

// Flash messages (simple session-based)
function setFlash($type, $message) {
    initSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    initSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Check if restaurant is currently open
function isRestaurantOpen() {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getConnection();

    $dias = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'];
    $hoy = $dias[date('w')];
    $horaActual = date('H:i:s');

    $stmt = $pdo->prepare("SELECT * FROM configuracion_horarios WHERE dia_semana = ?");
    $stmt->execute([$hoy]);
    $config = $stmt->fetch();

    if (!$config || !$config['abierto']) {
        return false;
    }

    return ($horaActual >= $config['hora_apertura'] && $horaActual <= $config['hora_cierre']);
}

// Format price
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Format date
function formatDate($date) {
    return date('Y-m-d H:i', strtotime($date));
}
