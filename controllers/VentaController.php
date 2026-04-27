<?php
/**
 * Controller: VentaController
 * Handles sales-related actions
 */

require_once __DIR__ . '/../services/VentaService.php';
require_once __DIR__ . '/../includes/helpers.php';

class VentaController {
    private $service;

    public function __construct() {
        $this->service = new VentaService();
    }

    // Get all sales
    public function index() {
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        return $this->service->getAll($fechaInicio, $fechaFin);
    }

    // Get daily summary
    public function dailySummary() {
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        return $this->service->getDailySummary($fecha);
    }

    // Get summary
    public function summary() {
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        return $this->service->getSummary($fechaInicio, $fechaFin);
    }

    // Update payment method for a sale
    public function updateMetodoPago() {
        $input = json_decode(file_get_contents('php://input'), true);
        $pedidoId = intval($input['pedido_id'] ?? 0);
        $metodoPago = $input['metodo_pago'] ?? 'efectivo';

        if ($pedidoId <= 0) {
            return ['success' => false, 'message' => 'Pedido inválido'];
        }

        $validMethods = ['efectivo', 'tarjeta', 'transferencia'];
        if (!in_array($metodoPago, $validMethods)) {
            return ['success' => false, 'message' => 'Método de pago inválido'];
        }

        return $this->service->updateMetodoPago($pedidoId, $metodoPago);
    }

    // Create sale
    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'pedido_id'   => intval($input['pedido_id'] ?? 0),
            'total'       => floatval($input['total'] ?? 0),
            'metodo_pago' => $input['metodo_pago'] ?? 'efectivo',
            'cajero_id'   => intval($input['cajero_id'] ?? $_SESSION['usuario_id'])
        ];

        if ($data['pedido_id'] <= 0) {
            return ['success' => false, 'message' => 'Pedido inválido'];
        }

        $id = $this->service->create($data);
        return ['success' => true, 'id' => $id, 'message' => 'Venta registrada exitosamente'];
    }
}
