<?php
/**
 * ============================================
 * SERVICIO DE PRODUCTOS
 * ============================================
 *
 * Maneja la lógica de negocio de productos.
 * Incluye la gestión de imágenes (subida y eliminación).
 *
 * Ejemplo de flujo para crear un producto:
 *   1. El controlador recibe los datos del formulario
 *   2. Llama a este servicio con los datos + archivo de imagen
 *   3. Este servicio sube la imagen y guarda el producto en BD
 *   4. Retorna el ID del nuevo producto
 */

require_once __DIR__ . '/../models/Producto.php';

class ProductoService {
    private $producto;

    public function __construct() {
        $this->producto = new Producto();
    }

    /**
     * Obtener todos los productos, opcionalmente filtrados por categoría.
     */
    public function getAll($categoriaId = null) {
        return $this->producto->getAll($categoriaId);
    }

    /**
     * Obtener un producto por su ID.
     */
    public function getById($id) {
        return $this->producto->findById($id);
    }

    /**
     * Crear un nuevo producto.
     * Si se envía un archivo de imagen, lo sube al servidor.
     *
     * @param array $data Datos del producto (nombre, precio, etc.)
     * @param array|null $file Archivo de imagen ($_FILES['imagen'])
     * @return int ID del producto creado
     */
    public function create($data, $file = null) {
        // Si hay archivo de imagen, subirlo primero
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $imageName = $this->uploadImage($file);
            if ($imageName) {
                $data['imagen'] = $imageName;
            }
        }

        return $this->producto->create($data);
    }

    /**
     * Actualizar un producto existente.
     * Si se envía nueva imagen, elimina la anterior del servidor.
     *
     * @param int $id ID del producto
     * @param array $data Datos a actualizar
     * @param array|null $file Nueva imagen (opcional)
     */
    public function update($id, $data, $file = null) {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $imageName = $this->uploadImage($file);
            if ($imageName) {
                // Eliminar imagen anterior para no acumular archivos
                $oldProduct = $this->producto->findById($id);
                if ($oldProduct && $oldProduct['imagen']) {
                    $this->deleteImage($oldProduct['imagen']);
                }
                $data['imagen'] = $imageName;
            }
        }

        return $this->producto->update($id, $data);
    }

    /**
     * Eliminar un producto y su imagen del servidor.
     */
    public function delete($id) {
        $product = $this->producto->findById($id);
        if ($product && $product['imagen']) {
            $this->deleteImage($product['imagen']);
        }
        return $this->producto->delete($id);
    }

    /**
     * Obtener todas las categorías activas.
     */
    public function getCategorias() {
        return $this->producto->getCategorias();
    }

    /**
     * Obtener solo productos activos (para el menú de pedidos).
     */
    public function getActive($categoriaId = null) {
        return $this->producto->getActive($categoriaId);
    }

    /**
     * Sube una imagen al servidor.
     *
     * Proceso:
     * 1. Verificar que el tipo MIME es una imagen válida
     * 2. Generar nombre único con uniqid() para evitar conflictos
     * 3. Crear carpeta de destino si no existe
     * 4. Mover el archivo temporal al destino final
     *
     * @param array $file Datos del archivo ($_FILES['imagen'])
     * @return string|null Nombre del archivo guardado o null si falla
     */
    private function uploadImage($file) {
        // Solo permitir tipos de imagen comunes
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        // Generar nombre único: prod_665a1b2c3d4e5.jpg
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('prod_') . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/productos/';

        // Crear carpeta si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // move_uploaded_file() mueve del temporal de PHP al destino final
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
            return $newName;
        }

        return null;
    }

    /**
     * Elimina una imagen del servidor.
     */
    private function deleteImage($imageName) {
        $path = __DIR__ . '/../uploads/productos/' . $imageName;
        if (file_exists($path)) {
            unlink($path); // unlink() elimina un archivo en PHP
        }
    }
}
