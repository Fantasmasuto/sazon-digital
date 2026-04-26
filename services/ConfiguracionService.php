<?php
/**
 * Service: ConfiguracionService
 * Handles restaurant configuration business logic
 */

require_once __DIR__ . '/../models/Configuracion.php';

class ConfiguracionService {
    private $config;

    public function __construct() {
        $this->config = new Configuracion();
    }

    // Get all schedules
    public function getHorarios() {
        return $this->config->getHorarios();
    }

    // Update schedule for a day
    public function updateHorario($diaSemana, $data) {
        return $this->config->updateHorario($diaSemana, $data);
    }

    // Update all schedules at once
    public function updateAllHorarios($horarios) {
        foreach ($horarios as $dia => $data) {
            $this->config->updateHorario($dia, $data);
        }
        return true;
    }

    // Check if restaurant is open
    public function isOpen() {
        return $this->config->isOpen();
    }

    // Get today's schedule
    public function getTodaySchedule() {
        return $this->config->getTodaySchedule();
    }
}
