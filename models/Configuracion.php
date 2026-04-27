<?php
/**
 * Model: Configuracion
 * Handles database operations for restaurant configuration
 */

require_once __DIR__ . '/../config/database.php';

class Configuracion {
    private $pdo;

    public function __construct() {
        $this->pdo = getConnection();
    }

    // Get all schedule configuration
    public function getHorarios() {
        $stmt = $this->pdo->query("SELECT * FROM configuracion_horarios ORDER BY FIELD(dia_semana, 'lunes','martes','miercoles','jueves','viernes','sabado','domingo')");
        return $stmt->fetchAll();
    }

    // Update schedule for a day
    public function updateHorario($diaSemana, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE configuracion_horarios
            SET abierto = ?, hora_apertura = ?, hora_cierre = ?
            WHERE dia_semana = ?
        ");
        return $stmt->execute([
            $data['abierto'] ? 1 : 0,
            $data['hora_apertura'],
            $data['hora_cierre'],
            $diaSemana
        ]);
    }

    // Check if restaurant is open right now
    public function isOpen() {
        $dias = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'];
        $hoy = $dias[date('w')];
        $horaActual = date('H:i:s');

        $stmt = $this->pdo->prepare("SELECT * FROM configuracion_horarios WHERE dia_semana = ?");
        $stmt->execute([$hoy]);
        $config = $stmt->fetch();

        if (!$config || !$config['abierto']) {
            return false;
        }

        return ($horaActual >= $config['hora_apertura'] && $horaActual <= $config['hora_cierre']);
    }

    // Get today's schedule
    public function getTodaySchedule() {
        $dias = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'];
        $hoy = $dias[date('w')];

        $stmt = $this->pdo->prepare("SELECT * FROM configuracion_horarios WHERE dia_semana = ?");
        $stmt->execute([$hoy]);
        return $stmt->fetch();
    }
}
