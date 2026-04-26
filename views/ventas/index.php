<?php
/**
 * View: Sales Report
 */
$pageTitle = 'Ventas';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Vista Ventas</h1>

<!-- Summary Cards -->
<div class="summary-cards" id="sales-summary">
    <div class="summary-card">
        <div class="card-label">Ingresos Totales</div>
        <div class="card-value" id="total-ingresos">$0.00</div>
    </div>
    <div class="summary-card">
        <div class="card-label">Ticket Promedio</div>
        <div class="card-value" id="ticket-promedio">$0.00</div>
    </div>
    <div class="summary-card">
        <div class="card-label">Total Ventas</div>
        <div class="card-value" id="total-ventas-count">0</div>
    </div>
</div>

<!-- Filters -->
<div class="data-table-wrapper">
    <div class="table-header">
        <h2>Historial de Ventas</h2>
        <div class="filters-bar">
            <label>Fecha Inicio</label>
            <input type="date" class="form-control" id="filter-fecha-inicio">
            <label>Fecha Fin</label>
            <input type="date" class="form-control" id="filter-fecha-fin">
            <button class="btn btn-primary" onclick="loadSales()">Aplicar Filtro</button>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th># Venta</th>
                <th>Pedido</th>
                <th>Fecha</th>
                <th>Cajero</th>
                <th>Total</th>
                <th>Método de Pago</th>
            </tr>
        </thead>
        <tbody id="sales-table-body">
            <tr><td colspan="6" class="empty-state">Cargando ventas...</td></tr>
        </tbody>
    </table>
</div>

<?php $pageScript = 'ventas.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
