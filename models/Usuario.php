<?php
/**
 * ============================================
 * MODELO: USUARIO
 * ============================================
 *
 * Este modelo maneja TODAS las operaciones de base de datos
 * relacionadas con usuarios. Los modelos SOLO contienen SQL.
 *
 * ¿Qué es un Modelo?
 * En la arquitectura de capas, el modelo es responsable de
 * comunicarse con la base de datos. No sabe nada de HTTP,
 * formularios ni HTML. Solo hace consultas SQL.
 *
 * Usamos PDO con Prepared Statements para prevenir inyección SQL.
 * Ejemplo peligroso: "SELECT * FROM usuarios WHERE email = '$email'"
 * Ejemplo seguro:    "SELECT * FROM usuarios WHERE email = ?" + [$email]
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $pdo;  // Conexión a la base de datos

    public function __construct() {
        $this->pdo = getConnection();
    }

    /**
     * Buscar usuario por ID.
     * JOIN con roles para obtener el nombre del rol.
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT u.*, r.nombre as rol_nombre
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();  // fetch() retorna un array o false
    }

    /**
     * Buscar usuario por email (usado en login).
     */
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

    /**
     * Obtener todos los usuarios con su información de rol.
     */
    public function getAll() {
        $stmt = $this->pdo->query("
            SELECT u.*, r.nombre as rol_nombre
            FROM usuarios u
            JOIN roles r ON u.rol_id = r.id
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll();  // fetchAll() retorna TODOS los registros
    }

    /**
     * Crear un nuevo usuario.
     * password_hash() encripta la contraseña antes de guardarla.
     * NUNCA guardes contraseñas en texto plano.
     */
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol_id, estado)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),  // Encriptar contraseña
            $data['rol_id'],
            $data['estado'] ?? 'activo'  // Valor por defecto si no se envía
        ]);
        return $this->pdo->lastInsertId();  // Retorna el ID auto-generado
    }

    /**
     * Actualizar usuario.
     * Solo actualiza los campos que se envían (actualización parcial).
     * La contraseña solo se actualiza si se envía una nueva.
     */
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

        $values[] = $id;  // Para el WHERE
        $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Eliminar un usuario.
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtener todos los roles disponibles.
     */
    public function getRoles() {
        $stmt = $this->pdo->query("SELECT * FROM roles ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Guardar token de "Recordarme" en la base de datos.
     * Se crea al hacer login con la opción "Recordarme" marcada.
     */
    public function saveToken($userId, $token, $expiry) {
        $stmt = $this->pdo->prepare("
            INSERT INTO tokens_login (usuario_id, token, expira_en)
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$userId, $token, $expiry]);
    }

    /**
     * Buscar usuario por token de "Recordarme".
     * Solo retorna si el token existe Y no ha expirado (expira_en > NOW()).
     */
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

    /**
     * Eliminar un token específico (al hacer logout).
     */
    public function deleteToken($token) {
        $stmt = $this->pdo->prepare("DELETE FROM tokens_login WHERE token = ?");
        return $stmt->execute([$token]);
    }

    /**
     * Eliminar todos los tokens de un usuario.
     */
    public function deleteUserTokens($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM tokens_login WHERE usuario_id = ?");
        return $stmt->execute([$userId]);
    }
}
