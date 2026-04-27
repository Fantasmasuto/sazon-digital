<?php
/**
 * View: Dashboard
 */
$pageTitle = 'Panel Principal';
$currentUser = getCurrentUser();
require_once __DIR__ . '/layout/header.php';
?>

<h1 class="page-title">Principal</h1>

<div id="dashboard-content">
    <div class="summary-cards" id="summary-cards">
        <div class="summary-card">
            <div class="card-label">Pedidos Hoy</div>
            <div class="card-value" id="pedidos-hoy">--</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Ventas Hoy</div>
            <div class="card-value" id="ventas-hoy">--</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Productos Activos</div>
            <div class="card-value" id="productos-activos">--</div>
        </div>
        <div class="summary-card">
            <div class="card-label">Mesas Disponibles</div>
            <div class="card-value" id="mesas-disponibles">--</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Pedidos Recientes</h3>
            <table class="data-table" id="recent-orders-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Mesa</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="recent-orders">
                    <tr><td colspan="4" class="empty-state">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="dashboard-card">
            <h3>Accesos Rápidos</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; padding: 10px 0;">
                <a href="index.php?page=pedido-crear" class="btn btn-primary" style="text-align: center;">Nuevo Pedido</a>
                <a href="index.php?page=productos" class="btn btn-info" style="text-align: center;">Ver Productos</a>
                <a href="index.php?page=reservaciones" class="btn btn-warning" style="text-align: center;">Reservaciones</a>
                <a href="index.php?page=cajero" class="btn btn-success" style="text-align: center;">Cajero</a>
                <?php if (hasRole('Administrador')): ?>
                <a href="index.php?page=ventas" class="btn" style="text-align: center; background: #8e44ad; color: #fff;">Ver Ventas</a>
                <a href="index.php?page=configuracion" class="btn btn-danger" style="text-align: center;">Configuración</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $pageScript = 'dashboard.js'; ?>
<?php require_once __DIR__ . '/layout/footer.php'; ?>
