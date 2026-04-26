<?php
/**
 * API: Ventas
 * GET /api/ventas.php                        - List all sales
 * GET /api/ventas.php?action=summary         - Get summary
 * POST /api/ventas.php                       - Create sale
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../controllers/VentaController.php';

initSession();
requireApiLogin();

$controller = new VentaController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Special actions
if ($action === 'summary') {
    jsonResponse($controller->summary());
}
if ($action === 'daily') {
    jsonResponse($controller->dailySummary());
}

switch ($method) {
    case 'GET':
        jsonResponse($controller->index());
        break;

    case 'POST':
        requireApiRole(['Administrador']);
        $result = $controller->store();
        jsonResponse($result, $result['success'] ? 201 : 400);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}
