<?php
/**
 * Model: Usuario
 * Handles database operations for users
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Find user by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.nombre as rol_nombre
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Find user by email
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.nombre as rol_nombre
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Get all users with role info
    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT u.*, r.nombre as rol_nombre
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll();
    }

    // Create new user
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol_id, estado)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['rol_id'],
            $data['estado'] ?? 'activo'
        ]);
        return $this->pdo->lastInsertId();
    }

    // Update user
    public function update($id, $data) {
        $fields = [];
        $values = [];

        if (isset($data['nombre'])) {
            $fields[] = "nombre = ?";
            $values[] = $data['nombre'];
        }
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $values[] = $data['email'];
        }
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['rol_id'])) {
            $fields[] = "rol_id = ?";
            $values[] = $data['rol_id'];
        }
        if (isset($data['estado'])) {
            $fields[] = "estado = ?";
            $values[] = $data['estado'];
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    // Delete user
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get all roles
    public function getRoles() {
        $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    // Save remember me token
    public function saveToken($userId, $token, $expiry) {
        $stmt = $this->pdo->prepare("
            INSERT INTO tokens_login (usuario_id, token, expira_en)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $token, $expiry]);
    }

    // Find user by remember token
    public function findByToken($token) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.nombre as rol_nombre, t.expira_en
            FROM tokens_login t
            JOIN usuarios u ON t.usuario_id = u.id
            JOIN roles r ON u.rol_id = r.id
            WHERE t.token = ? AND t.expira_en > NOW()
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    // Delete token
    public function deleteToken($token) {
        $stmt = $this->pdo->prepare("DELETE FROM tokens_login WHERE token = ?");
        return $stmt->execute([$token]);
    }

    // Delete all tokens for user
    public function deleteUserTokens($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM tokens_login WHERE usuario_id = ?");
        return $stmt->execute([$userId]);
    }
}
