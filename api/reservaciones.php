<?php
/**
 * API: Reservaciones
 * GET    /api/reservaciones.php          - List all
 * GET    /api/reservaciones.php?id=X     - Get one
 * POST   /api/reservaciones.php          - Create
 * PUT    /api/reservaciones.php?id=X     - Update
 * DELETE /api/reservaciones.php?id=X     - Delete
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../controllers/ReservacionController.php';

initSession();
requireApiLogin();

$controller = new ReservacionController();
$method = $_SERVER['REQUEST_METHOD'];
$id = intval($_GET['id'] ?? 0);

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $reservacion = $controller->show($id);
            if (!$reservacion) {
                jsonResponse(['error' => 'Reservación no encontrada'], 404);
            }
            jsonResponse($reservacion);
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
            $result = $controller->update($id);
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
