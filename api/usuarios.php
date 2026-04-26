<?php
/**
 * API: Usuarios
 * GET    /api/usuarios.php              - List all
 * GET    /api/usuarios.php?id=X         - Get one
 * POST   /api/usuarios.php              - Create
 * POST   /api/usuarios.php?id=X         - Update
 * DELETE /api/usuarios.php?id=X         - Delete
 * GET    /api/usuarios.php?action=roles - List roles
 */

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../models/Usuario.php';

initSession();
requireApiLogin();
requireApiRole(['Administrador']);

$usuario = new Usuario();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Special actions
if ($action === 'roles') {
    jsonResponse($usuario->getRoles());
}

switch ($method) {
    case 'GET':
        if ($id > 0) {
            $user = $usuario->findById($id);
            if (!$user) {
                jsonResponse(['error' => 'Usuario no encontrado'], 404);
            }
            unset($user['password']); // Don't expose password
            jsonResponse($user);
        } else {
            $users = $usuario->getAll();
            // Remove passwords from response
            foreach ($users as &$u) {
                unset($u['password']);
            }
            jsonResponse($users);
        }
        break;

    case 'POST':
        $data = [
            'nombre'   => htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'email'    => htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8'),
            'password' => $_POST['password'] ?? '',
            'rol_id'   => intval($_POST['rol_id'] ?? 0),
            'estado'   => htmlspecialchars(trim($_POST['estado'] ?? 'activo'), ENT_QUOTES, 'UTF-8')
        ];

        if ($id > 0) {
            // Update
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $usuario->update($id, $data);
            jsonResponse(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
        } else {
            // Create
            if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
                jsonResponse(['success' => false, 'message' => 'Datos incompletos'], 400);
            }
            $newId = $usuario->create($data);
            jsonResponse(['success' => true, 'id' => $newId, 'message' => 'Usuario creado exitosamente'], 201);
        }
        break;

    case 'DELETE':
        if ($id > 0) {
            // Prevent deleting yourself
            if ($id == $_SESSION['usuario_id']) {
                jsonResponse(['success' => false, 'message' => 'No puedes eliminar tu propio usuario'], 400);
            }
            $usuario->delete($id);
            jsonResponse(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        }
        jsonResponse(['error' => 'ID requerido'], 400);
        break;

    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
}
