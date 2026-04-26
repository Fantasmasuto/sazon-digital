<?php
/**
 * API: Authentication
 * POST /api/auth.php?action=login
 * POST /api/auth.php?action=logout
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../controllers/AuthController.php';

initSession();
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Método no permitido'], 405);
        }
        $controller = new AuthController();
        $result = $controller->login();
        jsonResponse($result, $result['success'] ? 200 : 401);
        break;

    case 'logout':
        $controller = new AuthController();
        $controller->logout();
        break;

    case 'status':
        jsonResponse([
            'logged_in' => isLoggedIn(),
            'user' => getCurrentUser()
        ]);
        break;

    default:
        jsonResponse(['error' => 'Acción no válida'], 400);
}
