<?php
/**
 * API: Pedidos
 * GET    /api/pedidos.php                    - List all
 * GET    /api/pedidos.php?id=X               - Get one with details
 * POST   /api/pedidos.php                    - Create
 * PUT    /api/pedidos.php?id=X               - Update status
 * DELETE /api/pedidos.php?id=X               - Delete
 * GET    /api/pedidos.php?action=estados      - Get order states
 * GET    /api/pedidos.php?action=mesas        - Get available tables
 * GET    /api/pedidos.php?action=kitchen      - Kitchen view
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../controllers/PedidoController.php';

initSession();
requireApiLogin();

$controller = new PedidoController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Special actions
if ($action === 'estados') {
    jsonResponse($controller->estados());
}
if ($action === 'mesas') {
    jsonResponse($controller->mesasDisponibles());
}
if ($action === 'kitchen') {
    jsonResponse($controller->kitchen());
}

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $pedido = $controller->show($id);
            if (!$pedido) {
                jsonResponse(['error' => 'Pedido no encontrado'], 404);
            }
            jsonResponse($pedido);
        } else {
            jsonResponse($controller->index());
        }
        break;

    case 'POST':
        requireApiRole(['Administrador', 'Mesero']);
        $result = $controller->store();
        jsonResponse($result, $result['success'] ? 201 : 400);
        break;

    case 'PUT':
        if ($id > 0) {
            $result = $controller->updateEstado($id);
            jsonResponse($result, $result['success'] ? 200 : 400);
        }
        jsonResponse(['error' => 'ID requerido'], 400);
        break;

    case 'DELETE':
        requireApiRole(['Administrador']);
        if ($id > 0) {
            $result = $controller->destroy($id);
            jsonResponse($result);
        }
        jsonResponse(['error' => 'ID requerido'], 400);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}
