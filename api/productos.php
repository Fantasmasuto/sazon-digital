<?php
/**
 * API: Productos
 * GET    /api/productos.php             - List all
 * GET    /api/productos.php?id=X        - Get one
 * POST   /api/productos.php             - Create
 * POST   /api/productos.php?id=X        - Update
 * DELETE /api/productos.php?id=X        - Delete
 * GET    /api/productos.php?action=categorias - List categories
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../controllers/ProductoController.php';

initSession();
requireApiLogin();

$controller = new ProductoController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Special actions
if ($action === 'categorias') {
    jsonResponse($controller->categorias());
}

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $product = $controller->show($id);
            if (!$product) {
                jsonResponse(['error' => 'Producto no encontrado'], 404);
            }
            jsonResponse($product);
        } else {
            jsonResponse($controller->index());
        }
        break;

    case 'POST':
        requireApiRole(['Administrador']);
        if ($id > 0) {
            // Update
            $result = $controller->update($id);
            jsonResponse($result);
        } else {
            // Create
            $result = $controller->store();
            jsonResponse($result, $result['success'] ? 201 : 400);
        }
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
