<?php
/**
 * View: Order Detail
 */
$id = intval($_GET['id'] ?? 0);
$pageTitle = 'Detalle del Pedido';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Detalle del Pedido #<?= $id ?></h1>

<div class="order-detail">
    <!-- Order Info & Products -->
    <div class="order-info" id="order-info">
        <h3>Información del Pedido</h3>
        <div class="order-meta" id="order-meta">
            <div class="order-meta-item">
                <div class="meta-label">Mesero</div>
                <div class="meta-value" id="detail-mesero">--</div>
            </div>
            <div class="order-meta-item">
                <div class="meta-label">Fecha</div>
                <div class="meta-value" id="detail-fecha">--</div>
            </div>
            <div class="order-meta-item">
                <div class="meta-label">Mesa</div>
                <div class="meta-value" id="detail-mesa">--</div>
            </div>
            <div class="order-meta-item">
                <div class="meta-label">Estado</div>
                <div class="meta-value" id="detail-estado">--</div>
            </div>
        </div>

        <div id="detail-notas" style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 6px; display: none;">
            <strong>Notas:</strong> <span id="detail-notas-text"></span>
        </div>

        <h3 style="margin-top: 15px;">Productos del Pedido</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody id="detail-products">
                <tr><td colspan="4" class="empty-state">Cargando...</td></tr>
            </tbody>
        </table>

        <div class="status-actions" id="status-actions">
            <!-- Status change buttons will be added by JS -->
        </div>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <h3>Resumen del Pedido</h3>
        <div style="margin-bottom: 15px;">
            <div class="total-label">Subtotal</div>
            <div class="total-value" id="detail-subtotal">$0.00</div>
        </div>
        <div style="margin-bottom: 15px;">
            <div class="total-label">Impuestos (IVA)</div>
            <div id="detail-tax" style="font-size: 1rem;">$0.00</div>
        </div>
        <div style="border-top: 2px solid #FF6B00; padding-top: 10px;">
            <div class="total-label">Total</div>
            <div class="total-value" id="detail-total">$0.00</div>
        </div>

        <div id="detail-status-badge" style="margin-top: 20px; text-align: center;"></div>

        <div style="margin-top: 15px;">
            <a href="index.php?page=pedidos" class="btn btn-info" style="width: 100%; text-align: center;">Volver a Pedidos</a>
        </div>
    </div>
</div>

<input type="hidden" id="pedido-id" value="<?= $id ?>">

<?php $pageScript = 'pedidos.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
