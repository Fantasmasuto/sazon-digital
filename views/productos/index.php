<?php
/**
 * View: Products List
 */
$pageTitle = 'Productos';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Vista Productos</h1>

<div class="data-table-wrapper">
    <div class="table-header">
        <div class="filters-bar">
            <label>Filtro por Categoría</label>
            <select class="form-control" id="filter-categoria" onchange="filterProducts()">
                <option value="">Todas</option>
            </select>
            <label>Nombre del producto</label>
            <input type="text" class="form-control" id="filter-nombre" placeholder="Buscar..." onkeyup="filterByName()">
        </div>
        <?php if (hasRole('Administrador')): ?>
        <a href="index.php?page=producto-form" class="btn btn-primary">Nuevo Producto</a>
        <?php endif; ?>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="products-table-body">
            <tr><td colspan="8" class="empty-state">Cargando productos...</td></tr>
        </tbody>
    </table>
</div>

<?php $pageScript = 'productos.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
