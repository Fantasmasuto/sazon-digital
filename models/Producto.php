<?php
/**
 * Model: Producto
 * Handles database operations for products
 */

require_once __DIR__ . '/../config/database.php';

class Producto {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Get all products with category info
    public function getAll($categoriaId = null) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id
        ";
        $params = [];

        if ($categoriaId) {
            $sql .= " WHERE p.categoria_id = ?";
            $params[] = $categoriaId;
        }

        $sql .= " ORDER BY p.id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Find product by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Create product
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO productos (nombre, descripcion, precio, categoria_id, imagen, estado)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['precio'],
            $data['categoria_id'],
            $data['imagen'] ?? null,
            $data['estado'] ?? 'activo'
        ]);
        return $this->pdo->lastInsertId();
    }

    // Update product
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach (['nombre', 'descripcion', 'precio', 'categoria_id', 'imagen', 'estado'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $sql = "UPDATE productos SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    // Delete product
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get all categories
    public function getCategorias() {
        $stmt = $this->pdo->query("SELECT * FROM categorias WHERE estado = 'activo' ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    // Get active products only
    public function getActive($categoriaId = null) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            JOIN categorias c ON p.categoria_id = c.id
            WHERE p.estado = 'activo'
        ";
        $params = [];

        if ($categoriaId) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $categoriaId;
        }

        $sql .= " ORDER BY p.nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
