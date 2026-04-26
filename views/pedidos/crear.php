<?php
/**
 * View: Create Order
 */
$pageTitle = 'Nuevo Pedido';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Crear Nuevo Pedido</h1>

<div class="order-create">
    <!-- Product Selection -->
    <div class="product-list-panel">
        <h3>Productos Disponibles</h3>
        <div class="filters-bar" style="margin-bottom: 15px;">
            <select class="form-control" id="order-cat-filter" onchange="filterOrderProducts()">
                <option value="">Todas las categorías</option>
            </select>
        </div>
        <div id="order-products-list">
            <div class="empty-state">Cargando productos...</div>
        </div>
    </div>

    <!-- Cart / Order Summary -->
    <div class="cart-panel">
        <h3>Resumen del Pedido</h3>

        <div class="form-group">
            <label for="order-mesa">Mesa</label>
            <select class="form-control" id="order-mesa">
                <option value="">Sin mesa (para llevar)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="order-notas">Notas del pedido</label>
            <textarea class="form-control" id="order-notas" rows="2" placeholder="Notas especiales..."></textarea>
        </div>

        <div id="cart-items">
            <div class="empty-state">Agrega productos al pedido</div>
        </div>

        <div class="cart-total">
            <span>Total: </span>
            <span class="total-amount" id="cart-total">$0.00</span>
        </div>

        <div class="form-actions" style="margin-top: 15px;">
            <button class="btn btn-primary" onclick="submitOrder()" style="flex: 1;">Crear Pedido</button>
            <a href="index.php?page=pedidos" class="btn btn-danger">Cancelar</a>
        </div>
    </div>
</div>

<?php $pageScript = 'pedidos.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
