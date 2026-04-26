<?php
/**
 * Service: ProductoService
 * Handles product business logic
 */

require_once __DIR__ . '/../models/Producto.php';

class ProductoService {
    private $producto;

    public function __construct() {
        $this->producto = new Producto();
    }

    // Get all products (with optional category filter)
    public function getAll($categoriaId = null) {
        return $this->producto->getAll($categoriaId);
    }

    // Get single product
    public function getById($id) {
        return $this->producto->findById($id);
    }

    // Create product with image upload
    public function create($data, $file = null) {
        // Handle image upload
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $imageName = $this->uploadImage($file);
            if ($imageName) {
                $data['imagen'] = $imageName;
            }
        }

        return $this->producto->create($data);
    }

    // Update product with optional image
    public function update($id, $data, $file = null) {
        // Handle image upload
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $imageName = $this->uploadImage($file);
            if ($imageName) {
                // Delete old image
                $oldProduct = $this->producto->findById($id);
                if ($oldProduct && $oldProduct['imagen']) {
                    $this->deleteImage($oldProduct['imagen']);
                }
                $data['imagen'] = $imageName;
            }
        }

        return $this->producto->update($id, $data);
    }

    // Delete product
    public function delete($id) {
        $product = $this->producto->findById($id);
        if ($product && $product['imagen']) {
            $this->deleteImage($product['imagen']);
        }
        return $this->producto->delete($id);
    }

    // Get categories
    public function getCategorias() {
        return $this->producto->getCategorias();
    }

    // Get active products
    public function getActive($categoriaId = null) {
        return $this->producto->getActive($categoriaId);
    }

    // Upload image file
    private function uploadImage($file) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('prod_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/productos/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            return $newName;
        }

        return null;
    }

    // Delete image file
    private function deleteImage($imageName) {
        $path = __DIR__ . '/../uploads/productos/' . $imageName;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
