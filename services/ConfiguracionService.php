<?php
/**
 * ============================================
 * SERVICIO DE CONFIGURACIÓN
 * ============================================
 *
 * Maneja la configuración de horarios del restaurante.
 *
 * Este servicio es fundamental para la regla de negocio:
 * "Cuando el restaurante está cerrado, se bloquean pedidos,
 *  ventas y reservaciones."
 *
 * El estado abierto/cerrado se consulta desde el middleware
 * y desde los helpers para mostrar banners en la interfaz.
 */

require_once __DIR__ . '/../models/Configuracion.php';

class ConfiguracionService {
    private $config;

    public function __construct() {
        $this->config = new Configuracion();
    }

    /**
     * Obtener horarios de todos los días de la semana.
     */
    public function getHorarios() {
        return $this->config->getHorarios();
    }

    /**
     * Actualizar horario de un día específico.
     */
    public function updateHorario($diaSemana, $data) {
        return $this->config->updateHorario($diaSemana, $data);
    }

    /**
     * Actualizar todos los horarios de una vez.
     * Se usa cuando el admin configura todos los días
     * y guarda todos los cambios al mismo tiempo.
     *
     * @param array $horarios Array asociativo {dia => {abierto, hora_apertura, hora_cierre}}
     */
    public function updateAllHorarios($horarios) {
        foreach ($horarios as $dia => $data) {
            $this->config->updateHorario($dia, $data);
        }
        return true;
    }

    /**
     * Verificar si el restaurante está abierto en este momento.
     */
    public function isOpen() {
        return $this->config->isOpen();
    }

    /**
     * Obtener el horario del día actual.
     */
    public function getTodaySchedule() {
        return $this->config->getTodaySchedule();
    }
}
