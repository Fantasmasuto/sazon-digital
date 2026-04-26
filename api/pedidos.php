<?php
/**
 * ============================================
 * API: PEDIDOS (ÓRDENES)
 * ============================================
 *
 * Este archivo es un "endpoint" REST que recibe peticiones
 * HTTP y responde con datos JSON.
 *
 * ¿Qué es REST?
 * Es una convención para organizar APIs web usando métodos HTTP:
 *   GET    = Leer datos
 *   POST   = Crear datos nuevos
 *   PUT    = Actualizar datos existentes
 *   DELETE = Eliminar datos
 *
 * Endpoints disponibles:
 *   GET    /api/pedidos.php                 - Listar todos los pedidos
 *   GET    /api/pedidos.php?id=X            - Ver un pedido con su detalle
 *   POST   /api/pedidos.php                 - Crear pedido (JSON)
 *   PUT    /api/pedidos.php?id=X            - Cambiar estado del pedido
 *   DELETE /api/pedidos.php?id=X            - Eliminar pedido
 *   GET    /api/pedidos.php?action=estados  - Listar estados
 *   GET    /api/pedidos.php?action=mesas    - Listar mesas disponibles
 *   GET    /api/pedidos.php?action=kitchen  - Pedidos para cocina
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../controllers/PedidoController.php';

// Iniciar sesión y verificar autenticación
initSession();
requireApiLogin();

$controller = new PedidoController();
$method = $_SERVER['REQUEST_METHOD'];     // GET, POST, PUT o DELETE
$action = $_GET['action'] ?? '';           // Acción especial (opcional)
$id = intval($_GET['id'] ?? 0);            // ID del pedido (opcional)

// --- Acciones especiales (no dependen del método HTTP) ---
if ($action === 'estados') {
    jsonResponse($controller->estados());
}
if ($action === 'mesas') {
    jsonResponse($controller->mesasDisponibles());
}
if ($action === 'kitchen') {
    jsonResponse($controller->kitchen());
}

// --- Enrutamiento según método HTTP ---
switch ($method) {

    // GET: Leer datos (listar todos o ver uno)
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

    // POST: Crear nuevo pedido (solo Admin y Mesero)
    case 'POST':
        requireApiRole(['Administrador', 'Mesero']);
        $result = $controller->store();
        jsonResponse($result, $result['success'] ? 201 : 400);
        break;

    // PUT: Actualizar estado del pedido
    case 'PUT':
        if ($id > 0) {
            $result = $controller->updateEstado($id);
            jsonResponse($result, $result['success'] ? 200 : 400);
        }
        jsonResponse(['error' => 'ID requerido'], 400);
        break;

    // DELETE: Eliminar pedido (solo Admin)
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
