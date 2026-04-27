<?php
/**
 * View: Cajero - Cobrar pedidos y corte de caja
 */
$pageTitle = 'Cajero';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Cajero</h1>

<!-- Corte de Caja -->
<div class="summary-cards" id="caja-summary">
    <div class="summary-card">
        <div class="card-label">Ventas del Día</div>
        <div class="card-value" id="caja-total-ventas">0</div>
    </div>
    <div class="summary-card">
        <div class="card-label">Ingresos del Día</div>
        <div class="card-value" id="caja-ingresos">$0.00</div>
    </div>
    <div class="summary-card">
        <div class="card-label">Efectivo</div>
        <div class="card-value" id="caja-efectivo">$0.00</div>
    </div>
    <div class="summary-card">
        <div class="card-label">Tarjeta</div>
        <div class="card-value" id="caja-tarjeta">$0.00</div>
    </div>
    <div class="summary-card">
        <div class="card-label">Transferencia</div>
        <div class="card-value" id="caja-transferencia">$0.00</div>
    </div>
</div>

<!-- Pedidos por cobrar -->
<div class="data-table-wrapper">
    <div class="table-header">
        <h2>Pedidos por Cobrar (Entregados)</h2>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th># Pedido</th>
                <th>Mesa</th>
                <th>Mesero</th>
                <th>Productos</th>
                <th>Total</th>
                <th>Método de Pago</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody id="pedidos-cobrar-body">
            <tr><td colspan="7" class="empty-state">Cargando pedidos...</td></tr>
        </tbody>
    </table>
</div>

<!-- Ventas del día -->
<div class="data-table-wrapper" style="margin-top: 20px;">
    <div class="table-header">
        <h2>Ventas Realizadas Hoy</h2>
        <button class="btn btn-primary" onclick="printCorteCaja()">Imprimir Corte de Caja</button>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th># Venta</th>
                <th>Pedido</th>
                <th>Hora</th>
                <th>Total</th>
                <th>Método de Pago</th>
                <th>Cajero</th>
            </tr>
        </thead>
        <tbody id="ventas-hoy-body">
            <tr><td colspan="6" class="empty-state">Cargando ventas...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal Cobrar -->
<div class="modal-overlay" id="cobrar-modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeCobrarModal()">&times;</button>
        <h2>Cobrar Pedido #<span id="cobrar-pedido-num"></span></h2>

        <div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <p><strong>Mesa:</strong> <span id="cobrar-mesa"></span></p>
            <p><strong>Mesero:</strong> <span id="cobrar-mesero"></span></p>
            <div id="cobrar-detalle"></div>
            <hr>
            <p style="font-size: 1.5rem; font-weight: bold; color: #FF6B00;">
                Total: <span id="cobrar-total"></span>
            </p>
        </div>

        <div class="form-group">
            <label for="metodo-pago"><strong>Método de Pago *</strong></label>
            <select class="form-control" id="metodo-pago">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
            </select>
        </div>

        <div class="form-actions">
            <button class="btn btn-success" onclick="confirmarCobro()" style="flex: 1; font-size: 1.1rem; padding: 12px;">
                Cobrar Pedido
            </button>
            <button class="btn btn-danger" onclick="closeCobrarModal()">Cancelar</button>
        </div>
    </div>
</div>

<?php $pageScript = 'cajero.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
