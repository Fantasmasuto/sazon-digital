<?php
/**
 * Middleware - Authentication and authorization checks
 */

require_once __DIR__ . '/helpers.php';

// Require login - redirect to login page if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        // Check for remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            require_once __DIR__ . '/../services/AuthService.php';
            $authService = new AuthService();
            if ($authService->loginWithToken($_COOKIE['remember_token'])) {
                return; // Successfully logged in with token
            }
        }
        redirect('index.php?page=login');
    }
}

// Require specific role
function requireRole($roles) {
    requireLogin();
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
    // No matching role - show access denied
    http_response_code(403);
    echo '<h1>Acceso Denegado</h1><p>No tienes permisos para acceder a esta sección.</p>';
    echo '<a href="index.php">Volver al inicio</a>';
    exit;
}

// Require restaurant to be open (for orders, sales, reservations)
function requireRestaurantOpen() {
    if (!isRestaurantOpen()) {
        setFlash('warning', 'El restaurante está cerrado en este momento. No se pueden realizar esta operación.');
        redirect('index.php?page=dashboard');
    }
}

// API authentication check - returns JSON error if not authenticated
function requireApiLogin() {
    initSession();
    if (!isLoggedIn()) {
        jsonResponse(['error' => 'No autenticado'], 401);
    }
}

// API role check
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
