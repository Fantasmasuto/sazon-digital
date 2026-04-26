<?php
/**
 * Controller: ProductoController
 * Handles product-related actions
 */

require_once __DIR__ . '/../services/ProductoService.php';
require_once __DIR__ . '/../includes/helpers.php';

class ProductoController {
    private $service;

    public function __construct() {
        $this->service = new ProductoService();
    }

    // List all products
    public function index() {
        $categoriaId = $_GET['categoria'] ?? null;
        return $this->service->getAll($categoriaId);
    }

    // Get single product
    public function show($id) {
        return $this->service->getById($id);
    }

    // Create product
    public function store() {
        $data = [
            'nombre'       => sanitize($_POST['nombre'] ?? ''),
            'descripcion'  => sanitize($_POST['descripcion'] ?? ''),
            'precio'       => floatval($_POST['precio'] ?? 0),
            'categoria_id' => intval($_POST['categoria_id'] ?? 0),
            'estado'       => sanitize($_POST['estado'] ?? 'activo')
        ];

        if (empty($data['nombre']) || $data['precio'] <= 0 || $data['categoria_id'] <= 0) {
            return ['success' => false, 'message' => 'Datos incompletos o inválidos'];
        }

        $file = $_FILES['imagen'] ?? null;
        $id = $this->service->create($data, $file);

        return ['success' => true, 'id' => $id, 'message' => 'Producto creado exitosamente'];
    }

    // Update product
    public function update($id) {
        $data = [];
        if (isset($_POST['nombre']))       $data['nombre'] = sanitize($_POST['nombre']);
        if (isset($_POST['descripcion']))  $data['descripcion'] = sanitize($_POST['descripcion']);
        if (isset($_POST['precio']))       $data['precio'] = floatval($_POST['precio']);
        if (isset($_POST['categoria_id'])) $data['categoria_id'] = intval($_POST['categoria_id']);
        if (isset($_POST['estado']))       $data['estado'] = sanitize($_POST['estado']);

        $file = $_FILES['imagen'] ?? null;
        $this->service->update($id, $data, $file);

        return ['success' => true, 'message' => 'Producto actualizado exitosamente'];
    }

    // Delete product
    public function destroy($id) {
        $this->service->delete($id);
        return ['success' => true, 'message' => 'Producto eliminado exitosamente'];
    }

    // Get categories
    public function categorias() {
        return $this->service->getCategorias();
    }
}
