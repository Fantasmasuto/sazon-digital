<?php
/**
 * View: Reservations
 */
$pageTitle = 'Reservaciones';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Reservaciones</h1>

<div class="data-table-wrapper">
    <div class="table-header">
        <div class="filters-bar">
            <label>Filtrar por fecha:</label>
            <input type="date" class="form-control" id="filter-fecha-reservacion" onchange="loadReservations()">
        </div>
        <?php if (hasRole('Administrador') || hasRole('Mesero')): ?>
        <button class="btn btn-primary" onclick="showReservationModal()">Nueva Reservación</button>
        <?php endif; ?>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th>Mesa</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Personas</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="reservations-table-body">
            <tr><td colspan="9" class="empty-state">Cargando reservaciones...</td></tr>
        </tbody>
    </table>
</div>

<!-- Reservation Modal -->
<div class="modal-overlay" id="reservation-modal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeReservationModal()">&times;</button>
        <h2 id="reservation-modal-title">Nueva Reservación</h2>
        <form id="reservation-form">
            <input type="hidden" id="reservation-id" value="">

            <div class="form-group">
                <label for="res-nombre">Nombre del Cliente *</label>
                <input type="text" class="form-control" id="res-nombre" required>
            </div>

            <div class="form-group">
                <label for="res-telefono">Teléfono (10 dígitos) *</label>
                <input type="tel" class="form-control" id="res-telefono" required maxlength="10" pattern="[0-9]{10}" placeholder="Ej: 5551234567">
            </div>

            <div class="form-group">
                <label for="res-email">Email</label>
                <input type="email" class="form-control" id="res-email">
            </div>

            <div class="form-group">
                <label for="res-mesa">Mesa *</label>
                <select class="form-control" id="res-mesa" required>
                    <option value="">Seleccionar mesa</option>
                </select>
            </div>

            <div class="form-group">
                <label for="res-fecha">Fecha *</label>
                <input type="date" class="form-control" id="res-fecha" required>
            </div>

            <div class="form-group">
                <label for="res-hora-inicio">Hora Inicio *</label>
                <input type="time" class="form-control" id="res-hora-inicio" required>
            </div>

            <div class="form-group">
                <label for="res-hora-fin">Hora Fin *</label>
                <input type="time" class="form-control" id="res-hora-fin" required>
            </div>

            <div class="form-group">
                <label for="res-personas">Número de Personas (máx. 10) *</label>
                <input type="number" class="form-control" id="res-personas" min="1" max="10" value="1" required>
            </div>

            <div class="form-group">
                <label for="res-notas">Notas</label>
                <textarea class="form-control" id="res-notas" rows="2"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Reservación</button>
                <button type="button" class="btn btn-danger" onclick="closeReservationModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php $pageScript = 'reservaciones.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
