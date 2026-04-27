<?php
/**
 * View: Product Form (Create/Edit)
 */
$id = intval($_GET['id'] ?? 0);
$pageTitle = $id > 0 ? 'Editar Producto' : 'Nuevo Producto';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title"><?= $pageTitle ?></h1>

<div class="form-container">
    <form id="product-form" enctype="multipart/form-data">
        <input type="hidden" id="product-id" value="<?= $id ?>">

        <div class="form-group">
            <label for="nombre">Nombre del Producto *</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="precio">Precio *</label>
            <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="categoria_id">Categoría *</label>
            <select class="form-control" id="categoria_id" name="categoria_id" required>
                <option value="">Seleccionar categoría</option>
            </select>
        </div>

        <div class="form-group">
            <label for="imagen">Imagen del Producto</label>
            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
            <div id="image-preview" style="margin-top: 10px;"></div>
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select class="form-control" id="estado" name="estado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Producto</button>
            <a href="index.php?page=productos" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>

<?php $pageScript = 'productos.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
