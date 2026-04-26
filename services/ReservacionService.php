<?php
/**
 * ============================================
 * SERVICIO DE RESERVACIONES
 * ============================================
 *
 * Maneja la lógica de negocio de reservaciones de mesas.
 *
 * Regla de negocio: Antes de crear o actualizar una reservación,
 * se verifica que la mesa esté disponible en el horario solicitado.
 * Si hay conflicto (otra reservación en el mismo horario y mesa),
 * se retorna un error.
 */

require_once __DIR__ . '/../models/Reservacion.php';

class ReservacionService {
    private $reservacion;

    public function __construct() {
        $this->reservacion = new Reservacion();
    }

    /**
     * Obtener todas las reservaciones, filtradas por fecha si se indica.
     */
    public function getAll($fecha = null) {
        return $this->reservacion->getAll($fecha);
    }

    /**
     * Obtener una reservación por su ID.
     */
    public function getById($id) {
        return $this->reservacion->findById($id);
    }

    /**
     * Crear una nueva reservación.
     * Primero verifica disponibilidad de la mesa.
     *
     * @param array $data Datos de la reservación
     * @return array {success: bool, id?: int, message: string}
     */
    public function create($data) {
        // Verificar disponibilidad antes de crear
        if (!$this->reservacion->isTableAvailable(
            $data['mesa_id'], $data['fecha'],
            $data['hora_inicio'], $data['hora_fin']
        )) {
            return ['success' => false, 'message' => 'La mesa no está disponible en ese horario.'];
        }

        $id = $this->reservacion->create($data);
        return ['success' => true, 'id' => $id, 'message' => 'Reservación creada exitosamente.'];
    }

    /**
     * Actualizar una reservación existente.
     * Si se cambian la mesa, fecha u hora, re-verifica disponibilidad.
     */
    public function update($id, $data) {
        // Re-verificar disponibilidad si cambia mesa/fecha/hora
        if (isset($data['mesa_id']) && isset($data['fecha']) &&
            isset($data['hora_inicio']) && isset($data['hora_fin'])) {
            if (!$this->reservacion->isTableAvailable(
                $data['mesa_id'], $data['fecha'],
                $data['hora_inicio'], $data['hora_fin'], $id // Excluir la reservación actual
            )) {
                return ['success' => false, 'message' => 'La mesa no está disponible en ese horario.'];
            }
        }

        $this->reservacion->update($id, $data);
        return ['success' => true, 'message' => 'Reservación actualizada exitosamente.'];
    }

    /**
     * Eliminar una reservación.
     */
    public function delete($id) {
        return $this->reservacion->delete($id);
    }
}
