<?php
/**
 * View: Orders List
 */
$pageTitle = 'Pedidos';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Vista Pedidos</h1>

<div class="data-table-wrapper">
    <div class="table-header">
        <div class="filters-bar">
            <label>Buscar pedido:</label>
            <input type="text" class="form-control" id="filter-pedido" placeholder="Buscar..." onkeyup="filterOrders()">
            <label>Gestión de pedidos</label>
        </div>
        <?php if (hasRole('Administrador') || hasRole('Mesero')): ?>
        <a href="index.php?page=pedido-crear" class="btn btn-primary">Nuevo Pedido</a>
        <?php endif; ?>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Pedido</th>
                <th>Mesero</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="orders-table-body">
            <tr><td colspan="7" class="empty-state">Cargando pedidos...</td></tr>
        </tbody>
    </table>
</div>

<?php $pageScript = 'pedidos.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
