<?php
/**
 * Controller: ReservacionController
 * Handles reservation-related actions
 */

require_once __DIR__ . '/../services/ReservacionService.php';
require_once __DIR__ . '/../includes/helpers.php';

class ReservacionController {
    private $service;

    public function __construct() {
        $this->service = new ReservacionService();
    }

    // List all reservations
    public function index() {
        $fecha = $_GET['fecha'] ?? null;
        return $this->service->getAll($fecha);
    }

    // Get single reservation
    public function show($id) {
        return $this->service->getById($id);
    }

    // Create reservation
    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'cliente_nombre'   => sanitize($input['cliente_nombre'] ?? ''),
            'cliente_telefono' => sanitize($input['cliente_telefono'] ?? ''),
            'cliente_email'    => sanitize($input['cliente_email'] ?? ''),
            'mesa_id'          => intval($input['mesa_id'] ?? 0),
            'fecha'            => $input['fecha'] ?? '',
            'hora_inicio'      => $input['hora_inicio'] ?? '',
            'hora_fin'         => $input['hora_fin'] ?? '',
            'num_personas'     => intval($input['num_personas'] ?? 1),
            'notas'            => sanitize($input['notas'] ?? '')
        ];

        if (empty($data['cliente_nombre']) || empty($data['fecha']) || $data['mesa_id'] <= 0) {
            return ['success' => false, 'message' => 'Datos incompletos'];
        }

        // Validar teléfono: exactamente 10 dígitos
        if (!preg_match('/^\d{10}$/', $data['cliente_telefono'])) {
            return ['success' => false, 'message' => 'El teléfono debe tener exactamente 10 dígitos numéricos'];
        }

        // Validar fecha: solo hoy en adelante
        if ($data['fecha'] < date('Y-m-d')) {
            return ['success' => false, 'message' => 'No se puede reservar en fechas pasadas'];
        }

        // Validar hora inicio < hora fin
        if ($data['hora_inicio'] >= $data['hora_fin']) {
            return ['success' => false, 'message' => 'La hora de inicio debe ser antes que la hora de fin'];
        }

        // Validar máximo 10 personas
        if ($data['num_personas'] < 1 || $data['num_personas'] > 10) {
            return ['success' => false, 'message' => 'El número de personas debe ser entre 1 y 10'];
        }

        return $this->service->create($data);
    }

    // Update reservation
    public function update($id) {
        $input = json_decode(file_get_contents('php://input'), true);

        $data = [];
        foreach (['cliente_nombre', 'cliente_telefono', 'cliente_email', 'fecha', 'hora_inicio', 'hora_fin', 'notas', 'estado'] as $field) {
            if (isset($input[$field])) {
                $data[$field] = sanitize($input[$field]);
            }
        }
        if (isset($input['mesa_id'])) $data['mesa_id'] = intval($input['mesa_id']);
        if (isset($input['num_personas'])) $data['num_personas'] = intval($input['num_personas']);

        return $this->service->update($id, $data);
    }

    // Delete reservation
    public function destroy($id) {
        $this->service->delete($id);
        return ['success' => true, 'message' => 'Reservación eliminada exitosamente'];
    }
}
