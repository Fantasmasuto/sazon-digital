<?php
/**
 * Service: PedidoService
 * Handles order business logic
 */

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Venta.php';

class PedidoService {
    private $pedido;

    public function __construct() {
        $this->pedido = new Pedido();
    }

    // Get all orders
    public function getAll($estadoId = null) {
        return $this->pedido->getAll($estadoId);
    }

    // Get order by ID with details
    public function getById($id) {
        $pedido = $this->pedido->findById($id);
        if ($pedido) {
            $pedido['detalle'] = $this->pedido->getDetalle($id);
        }
        return $pedido;
    }

    // Create order
    public function create($data) {
        return $this->pedido->create($data);
    }

    // Update order status
    public function updateEstado($id, $estadoId) {
        $result = $this->pedido->updateEstado($id, $estadoId);

        // If status is "Entregado" (5), auto-create sale
        if ($estadoId == 5) {
            $pedido = $this->pedido->findById($id);
            $venta = new Venta();
            if (!$venta->existsForOrder($id)) {
                $venta->create([
                    'pedido_id' => $id,
                    'total'     => $pedido['total'],
                    'cajero_id' => $_SESSION['usuario_id'] ?? $pedido['mesero_id']
                ]);
            }
        }

        return $result;
    }

    // Delete order
    public function delete($id) {
        return $this->pedido->delete($id);
    }

    // Get order states
    public function getEstados() {
        return $this->pedido->getEstados();
    }

    // Get available tables
    public function getMesasDisponibles() {
        return $this->pedido->getMesasDisponibles();
    }

    // Get all tables
    public function getMesas() {
        return $this->pedido->getMesas();
    }

    // Get kitchen orders
    public function getForKitchen() {
        return $this->pedido->getForKitchen();
    }

    // Get order detail
    public function getDetalle($pedidoId) {
        return $this->pedido->getDetalle($pedidoId);
    }
}
