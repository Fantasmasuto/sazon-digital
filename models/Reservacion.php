<?php
/**
 * Model: Reservacion
 * Handles database operations for reservations
 */

require_once __DIR__ . '/../config/database.php';

class Reservacion {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Get all reservations
    public function getAll($fecha = null) {
        $sql = "
            SELECT r.*, m.numero as mesa_numero, m.capacidad as mesa_capacidad
            FROM reservaciones r
            JOIN mesas m ON r.mesa_id = m.id
        ";
        $params = [];

        if ($fecha) {
            $sql .= " WHERE r.fecha = ?";
            $params[] = $fecha;
        }

        $sql .= " ORDER BY r.fecha ASC, r.hora_inicio ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Find by ID
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, m.numero as mesa_numero, m.capacidad as mesa_capacidad
            FROM reservaciones r
            JOIN mesas m ON r.mesa_id = m.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Create reservation
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO reservaciones (cliente_nombre, cliente_telefono, cliente_email, mesa_id, fecha, hora_inicio, hora_fin, num_personas, estado, notas)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)
        ");
        $stmt->execute([
            $data['cliente_nombre'],
            $data['cliente_telefono'] ?? '',
            $data['cliente_email'] ?? '',
            $data['mesa_id'],
            $data['fecha'],
            $data['hora_inicio'],
            $data['hora_fin'],
            $data['num_personas'] ?? 1,
            $data['notas'] ?? ''
        ]);
        return $this->pdo->lastInsertId();
    }

    // Update reservation
    public function update($id, $data) {
        $fields = [];
        $values = [];

        foreach (['cliente_nombre', 'cliente_telefono', 'cliente_email', 'mesa_id', 'fecha', 'hora_inicio', 'hora_fin', 'num_personas', 'estado', 'notas'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $sql = "UPDATE reservaciones SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    // Delete reservation
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM reservaciones WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Check table availability
    public function isTableAvailable($mesaId, $fecha, $horaInicio, $horaFin, $excludeId = null) {
        $sql = "
            SELECT COUNT(*) as count FROM reservaciones
            WHERE mesa_id = ? AND fecha = ?
            AND estado NOT IN ('cancelada', 'completada')
            AND (
                (hora_inicio < ? AND hora_fin > ?)
                OR (hora_inicio < ? AND hora_fin > ?)
                OR (hora_inicio >= ? AND hora_fin <= ?)
            )
        ";
        $params = [$mesaId, $fecha, $horaFin, $horaInicio, $horaFin, $horaInicio, $horaInicio, $horaFin];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }
}
