<?php
/**
 * ============================================
 * SERVICIO DE PEDIDOS
 * ============================================
 *
 * Maneja la lógica de negocio de pedidos (órdenes).
 *
 * Regla de negocio importante:
 * Cuando un pedido se marca como "Finalizado" (estado_id = 5),
 * se crea automáticamente una venta en la tabla de ventas.
 * Finalizado = el cliente ya pagó. Esto cierra el ciclo:
 * Pedido → Entregado → Finalizado (pagado) → Venta → Reporte
 */

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Venta.php';

class PedidoService {
    private $pedido;

    public function __construct() {
        $this->pedido = new Pedido();
    }

    /**
     * Obtener todos los pedidos, opcionalmente filtrados por estado.
     */
    public function getAll($estadoId = null) {
        return $this->pedido->getAll($estadoId);
    }

    /**
     * Obtener un pedido por ID, incluyendo sus productos (detalle).
     * Combina la información del pedido con su detalle en un solo array.
     */
    public function getById($id) {
        $pedido = $this->pedido->findById($id);
        if ($pedido) {
            // Agregar los productos del pedido al resultado
            $pedido['detalle'] = $this->pedido->getDetalle($id);
        }
        return $pedido;
    }

    /**
     * Crear un nuevo pedido con sus productos.
     * El modelo maneja la transacción para insertar pedido + detalles.
     */
    public function create($data) {
        return $this->pedido->create($data);
    }

    /**
     * Cambiar el estado de un pedido.
     *
     * REGLA DE NEGOCIO: Si el nuevo estado es "Finalizado" (5),
     * se crea una venta automáticamente con el total del pedido.
     * Finalizado significa que el cliente pagó y el pedido se cierra.
     * El cajero registrado es el usuario que cambió el estado.
     *
     * @param int $id ID del pedido
     * @param int $estadoId Nuevo estado
     */
    public function updateEstado($id, $estadoId) {
        $result = $this->pedido->updateEstado($id, $estadoId);

        // Auto-crear venta al finalizar el pedido (pagado)
        if ($estadoId == 5) {
            $pedido = $this->pedido->findById($id);
            $venta = new Venta();

            // Verificar que no exista ya una venta para este pedido
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

    /**
     * Eliminar un pedido.
     */
    public function delete($id) {
        return $this->pedido->delete($id);
    }

    /**
     * Obtener la lista de estados posibles para un pedido.
     */
    public function getEstados() {
        return $this->pedido->getEstados();
    }

    /**
     * Obtener mesas disponibles (no ocupadas).
     */
    public function getMesasDisponibles() {
        return $this->pedido->getMesasDisponibles();
    }

    /**
     * Obtener todas las mesas (disponibles y ocupadas).
     */
    public function getMesas() {
        return $this->pedido->getMesas();
    }

    /**
     * Obtener pedidos activos para la vista de cocina.
     * Excluye pedidos entregados y cancelados.
     */
    public function getForKitchen() {
        return $this->pedido->getForKitchen();
    }

    /**
     * Obtener el detalle (productos) de un pedido.
     */
    public function getDetalle($pedidoId) {
        return $this->pedido->getDetalle($pedidoId);
    }
}
