<?php
/**
 * Controller: ConfiguracionController
 * Handles restaurant configuration actions
 */

require_once __DIR__ . '/../services/ConfiguracionService.php';
require_once __DIR__ . '/../includes/helpers.php';

class ConfiguracionController {
    private $service;

    public function __construct() {
        $this->service = new ConfiguracionService();
    }

    // Get all schedules
    public function getHorarios() {
        return $this->service->getHorarios();
    }

    // Update schedules
    public function updateHorarios() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['horarios']) || !is_array($input['horarios'])) {
            return ['success' => false, 'message' => 'Datos inválidos'];
        }

        $this->service->updateAllHorarios($input['horarios']);
        return ['success' => true, 'message' => 'Horarios actualizados exitosamente'];
    }

    // Check restaurant status
    public function status() {
        return [
            'abierto' => $this->service->isOpen(),
            'horario_hoy' => $this->service->getTodaySchedule()
        ];
    }
}
