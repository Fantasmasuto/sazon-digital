<?php
/**
 * ============================================
 * SERVICIO DE VENTAS
 * ============================================
 *
 * Maneja la lógica de negocio de ventas.
 * Las ventas se generan automáticamente al entregar un pedido
 * (ver PedidoService), pero también se pueden crear manualmente.
 */

require_once __DIR__ . '/../models/Venta.php';

class VentaService {
    private $venta;

    public function __construct() {
        $this->venta = new Venta();
    }

    /**
     * Obtener ventas con filtros opcionales de fecha.
     */
    public function getAll($fechaInicio = null, $fechaFin = null) {
        return $this->venta->getAll($fechaInicio, $fechaFin);
    }

    /**
     * Crear una venta manualmente.
     */
    public function create($data) {
        return $this->venta->create($data);
    }

    /**
     * Obtener resumen del día: total ventas, ingresos, ticket promedio.
     */
    public function getDailySummary($fecha = null) {
        return $this->venta->getDailySummary($fecha);
    }

    /**
     * Obtener resumen para un rango de fechas.
     */
    public function getSummary($fechaInicio = null, $fechaFin = null) {
        return $this->venta->getSummary($fechaInicio, $fechaFin);
    }

    /**
     * Actualizar el método de pago de una venta por su pedido_id.
     */
    public function updateMetodoPago($pedidoId, $metodoPago) {
        return $this->venta->updateMetodoPago($pedidoId, $metodoPago);
    }
}
