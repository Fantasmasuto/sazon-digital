<?php
/**
 * Controller: PedidoController
 * Handles order-related actions
 */

require_once __DIR__ . '/../services/PedidoService.php';
require_once __DIR__ . '/../includes/helpers.php';

class PedidoController {
    private $service;

    public function __construct() {
        $this->service = new PedidoService();
    }

    // List all orders
    public function index() {
        $estadoId = $_GET['estado'] ?? null;
        return $this->service->getAll($estadoId);
    }

    // Get single order with details
    public function show($id) {
        return $this->service->getById($id);
    }

    // Create order
    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);

        $data = [
            'mesa_id'    => intval($input['mesa_id'] ?? 0),
            'mesero_id'  => intval($input['mesero_id'] ?? $_SESSION['usuario_id']),
            'notas'      => $input['notas'] ?? '',
            'productos'  => $input['productos'] ?? []
        ];

        if (empty($data['productos'])) {
            return ['success' => false, 'message' => 'Debe agregar al menos un producto al pedido'];
        }

        $id = $this->service->create($data);
        return ['success' => true, 'id' => $id, 'message' => 'Pedido creado exitosamente'];
    }

    // Update order status
    public function updateEstado($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $estadoId = intval($input['estado_id'] ?? 0);

        if ($estadoId <= 0) {
            return ['success' => false, 'message' => 'Estado inválido'];
        }

        $this->service->updateEstado($id, $estadoId);
        return ['success' => true, 'message' => 'Estado del pedido actualizado'];
    }

    // Delete order
    public function destroy($id) {
        $this->service->delete($id);
        return ['success' => true, 'message' => 'Pedido eliminado exitosamente'];
    }

    // Get states
    public function estados() {
        return $this->service->getEstados();
    }

    // Get available tables
    public function mesasDisponibles() {
        return $this->service->getMesasDisponibles();
    }

    // Get kitchen orders
    public function kitchen() {
        return $this->service->getForKitchen();
    }
}
