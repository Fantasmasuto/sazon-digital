<?php
/**
 * API: Configuración
 * GET  /api/configuracion.php                  - Get schedules
 * GET  /api/configuracion.php?action=status     - Restaurant status
 * POST /api/configuracion.php                   - Update schedules
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../controllers/ConfiguracionController.php';

initSession();
requireApiLogin();

$controller = new ConfiguracionController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Status check (available to all roles)
if ($action === 'status') {
    jsonResponse($controller->status());
}

switch ($method) {
    case 'GET':
        jsonResponse($controller->getHorarios());
        break;

    case 'POST':
        requireApiRole(['Administrador']);
        $result = $controller->updateHorarios();
        jsonResponse($result, $result['success'] ? 200 : 400);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}
