<?php
/**
 * Service: ReservacionService
 * Handles reservation business logic
 */

require_once __DIR__ . '/../models/Reservacion.php';

class ReservacionService {
    private $reservacion;

    public function __construct() {
        $this->reservacion = new Reservacion();
    }

    // Get all reservations
    public function getAll($fecha = null) {
        return $this->reservacion->getAll($fecha);
    }

    // Get reservation by ID
    public function getById($id) {
        return $this->reservacion->findById($id);
    }

    // Create reservation (checks availability)
    public function create($data) {
        // Check table availability
        if (!$this->reservacion->isTableAvailable($data['mesa_id'], $data['fecha'], $data['hora_inicio'], $data['hora_fin'])) {
            return ['success' => false, 'message' => 'La mesa no está disponible en ese horario.'];
        }

        $id = $this->reservacion->create($data);
        return ['success' => true, 'id' => $id, 'message' => 'Reservación creada exitosamente.'];
    }

    // Update reservation
    public function update($id, $data) {
        // Check availability if date/time/table changed
        if (isset($data['mesa_id']) && isset($data['fecha']) && isset($data['hora_inicio']) && isset($data['hora_fin'])) {
            if (!$this->reservacion->isTableAvailable($data['mesa_id'], $data['fecha'], $data['hora_inicio'], $data['hora_fin'], $id)) {
                return ['success' => false, 'message' => 'La mesa no está disponible en ese horario.'];
            }
        }

        $this->reservacion->update($id, $data);
        return ['success' => true, 'message' => 'Reservación actualizada exitosamente.'];
    }

    // Delete reservation
    public function delete($id) {
        return $this->reservacion->delete($id);
    }
}
