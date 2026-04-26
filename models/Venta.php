<?php
/**
 * Model: Venta
 * Handles database operations for sales
 */

require_once __DIR__ . '/../config/database.php';

class Venta {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Get all sales with filters
    public function getAll($fechaInicio = null, $fechaFin = null) {
        $sql = "
            SELECT v.*, p.id as pedido_numero, u.nombre as cajero_nombre
            FROM ventas v
            JOIN pedidos p ON v.pedido_id = p.id
            JOIN usuarios u ON v.cajero_id = u.id
        ";
        $params = [];
        $conditions = [];

        if ($fechaInicio) {
            $conditions[] = "DATE(v.created_at) >= ?";
            $params[] = $fechaInicio;
        }
        if ($fechaFin) {
            $conditions[] = "DATE(v.created_at) <= ?";
            $params[] = $fechaFin;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY v.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Create sale from order
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO ventas (pedido_id, total, metodo_pago, cajero_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['pedido_id'],
            $data['total'],
            $data['metodo_pago'] ?? 'efectivo',
            $data['cajero_id']
        ]);
        return $this->pdo->lastInsertId();
    }

    // Get daily summary
    public function getDailySummary($fecha = null) {
        if (!$fecha) $fecha = date('Y-m-d');

        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as total_ventas,
                COALESCE(SUM(total), 0) as ingresos_totales,
                COALESCE(AVG(total), 0) as ticket_promedio
            FROM ventas
            WHERE DATE(created_at) = ?
        ");
        $stmt->execute([$fecha]);
        return $stmt->fetch();
    }

    // Get sales by date range summary
    public function getSummary($fechaInicio = null, $fechaFin = null) {
        $sql = "
            SELECT
                COUNT(*) as total_ventas,
                COALESCE(SUM(total), 0) as ingresos_totales,
                COALESCE(AVG(total), 0) as ticket_promedio
            FROM ventas
        ";
        $params = [];
        $conditions = [];

        if ($fechaInicio) {
            $conditions[] = "DATE(created_at) >= ?";
            $params[] = $fechaInicio;
        }
        if ($fechaFin) {
            $conditions[] = "DATE(created_at) <= ?";
            $params[] = $fechaFin;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // Check if sale exists for order
    public function existsForOrder($pedidoId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM ventas WHERE pedido_id = ?");
        $stmt->execute([$pedidoId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
