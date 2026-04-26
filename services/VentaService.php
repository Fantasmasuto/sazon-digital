<?php
/**
 * Service: VentaService
 * Handles sales business logic
 */

require_once __DIR__ . '/../models/Venta.php';

class VentaService {
    private $venta;

    public function __construct() {
        $this->venta = new Venta();
    }

    // Get all sales with optional filters
    public function getAll($fechaInicio = null, $fechaFin = null) {
        return $this->venta->getAll($fechaInicio, $fechaFin);
    }

    // Create sale
    public function create($data) {
        return $this->venta->create($data);
    }

    // Get daily summary
    public function getDailySummary($fecha = null) {
        return $this->venta->getDailySummary($fecha);
    }

    // Get summary for date range
    public function getSummary($fechaInicio = null, $fechaFin = null) {
        return $this->venta->getSummary($fechaInicio, $fechaFin);
    }
}
