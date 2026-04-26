<?php
/**
 * Model: Pedido
 * Handles database operations for orders
 */

require_once __DIR__ . '/../config/database.php';

class Pedido {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Get all orders with related info
    public function getAll($estado_id = null) {
        $sql = "
            SELECT p.*, e.nombre as estado_nombre, e.color as estado_color,
                   u.nombre as mesero_nombre, m.numero as mesa_numero
            FROM pedidos p
            JOIN estados_pedido e ON p.estado_id = e.id
            JOIN usuarios u ON p.mesero_id = u.id
            LEFT JOIN mesas m ON p.mesa_id = m.id
            ORDER BY p.created_at DESC
        ";

        if ($estado_id) {
            $sql = "
                SELECT p.*, e.nombre as estado_nombre, e.color as estado_color,
                       u.nombre as mesero_nombre, m.numero as mesa_numero
                FROM pedidos p
                JOIN estados_pedido e ON p.estado_id = e.id
                JOIN usuarios u ON p.mesero_id = u.id
                LEFT JOIN mesas m ON p.mesa_id = m.id
                WHERE p.estado_id = ?
                ORDER BY p.created_at DESC
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$estado_id]);
            return $stmt->fetchAll();
        }

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Find order by ID with full details
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, e.nombre as estado_nombre, e.color as estado_color,
                   u.nombre as mesero_nombre, m.numero as mesa_numero
            FROM pedidos p
            JOIN estados_pedido e ON p.estado_id = e.id
            JOIN usuarios u ON p.mesero_id = u.id
            LEFT JOIN mesas m ON p.mesa_id = m.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Get order detail items
    public function getDetalle($pedidoId) {
        $stmt = $this->pdo->prepare("
            SELECT d.*, pr.nombre as producto_nombre, pr.imagen as producto_imagen
            FROM detalle_pedido d
            JOIN productos pr ON d.producto_id = pr.id
            WHERE d.pedido_id = ?
        ");
        $stmt->execute([$pedidoId]);
        return $stmt->fetchAll();
    }

    // Create order
    public function create($data) {
        $this->pdo->beginTransaction();
        try {
            // Insert order
            $stmt = $this->pdo->prepare("
                INSERT INTO pedidos (mesa_id, mesero_id, estado_id, notas, total)
                VALUES (?, ?, 1, ?, ?)
            ");
            $stmt->execute([
                $data['mesa_id'] ?: null,
                $data['mesero_id'],
                $data['notas'] ?? '',
                $data['total'] ?? 0
            ]);
            $pedidoId = $this->pdo->lastInsertId();

            // Insert order details
            if (isset($data['productos']) && is_array($data['productos'])) {
                $stmtDetalle = $this->pdo->prepare("
                    INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, notas)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $total = 0;
                foreach ($data['productos'] as $item) {
                    $subtotal = $item['cantidad'] * $item['precio_unitario'];
                    $stmtDetalle->execute([
                        $pedidoId,
                        $item['producto_id'],
                        $item['cantidad'],
                        $item['precio_unitario'],
                        $subtotal,
                        $item['notas'] ?? ''
                    ]);
                    $total += $subtotal;
                }

                // Update total
                $stmtTotal = $this->pdo->prepare("UPDATE pedidos SET total = ? WHERE id = ?");
                $stmtTotal->execute([$total, $pedidoId]);
            }

            // Update mesa status
            if (!empty($data['mesa_id'])) {
                $stmtMesa = $this->pdo->prepare("UPDATE mesas SET estado = 'ocupada' WHERE id = ?");
                $stmtMesa->execute([$data['mesa_id']]);
            }

            $this->pdo->commit();
            return $pedidoId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Update order status
    public function updateEstado($id, $estadoId) {
        $stmt = $this->pdo->prepare("UPDATE pedidos SET estado_id = ? WHERE id = ?");
        $result = $stmt->execute([$estadoId, $id]);

        // If order is finalized or cancelled, free the table
        if ($estadoId >= 5) { // Entregado or Cancelado
            $pedido = $this->findById($id);
            if ($pedido && $pedido['mesa_id']) {
                $stmtMesa = $this->pdo->prepare("UPDATE mesas SET estado = 'disponible' WHERE id = ?");
                $stmtMesa->execute([$pedido['mesa_id']]);
            }
        }

        return $result;
    }

    // Delete order
    public function delete($id) {
        $pedido = $this->findById($id);
        if ($pedido && $pedido['mesa_id']) {
            $stmtMesa = $this->pdo->prepare("UPDATE mesas SET estado = 'disponible' WHERE id = ?");
            $stmtMesa->execute([$pedido['mesa_id']]);
        }
        $stmt = $this->pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Get all order states
    public function getEstados() {
        $stmt = $this->pdo->query("SELECT * FROM estados_pedido ORDER BY orden ASC");
        return $stmt->fetchAll();
    }

    // Get available tables
    public function getMesasDisponibles() {
        $stmt = $this->pdo->query("SELECT * FROM mesas WHERE estado = 'disponible' ORDER BY numero ASC");
        return $stmt->fetchAll();
    }

    // Get all tables
    public function getMesas() {
        $stmt = $this->pdo->query("SELECT * FROM mesas ORDER BY numero ASC");
        return $stmt->fetchAll();
    }

    // Get orders for kitchen view (not finalized/cancelled)
    public function getForKitchen() {
        $stmt = $this->pdo->query("
            SELECT p.*, e.nombre as estado_nombre, e.color as estado_color,
                   u.nombre as mesero_nombre, m.numero as mesa_numero
            FROM pedidos p
            JOIN estados_pedido e ON p.estado_id = e.id
            JOIN usuarios u ON p.mesero_id = u.id
            LEFT JOIN mesas m ON p.mesa_id = m.id
            WHERE p.estado_id NOT IN (5, 6)
            ORDER BY p.created_at ASC
        ");
        return $stmt->fetchAll();
    }
}
