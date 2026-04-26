<?php
/**
 * View: Clients List (from reservations data)
 */
$pageTitle = 'Clientes';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Gestión de Clientes</h1>

<div class="data-table-wrapper">
    <div class="table-header">
        <div class="filters-bar">
            <label>Buscar Cliente:</label>
            <input type="text" class="form-control" id="filter-cliente" placeholder="Nombre, Email o Teléfono..." onkeyup="filterClients()">
        </div>
        <button class="btn btn-primary" onclick="showReservationForClient()">Nueva Reservación</button>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="clients-table-body">
            <tr><td colspan="6" class="empty-state">Cargando clientes...</td></tr>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadClients();
});

function loadClients() {
    fetch('api/reservaciones.php')
        .then(r => r.json())
        .then(data => {
            // Extract unique clients from reservations
            const clientsMap = {};
            data.forEach(res => {
                const key = res.cliente_email || res.cliente_nombre;
                if (!clientsMap[key]) {
                    clientsMap[key] = {
                        nombre: res.cliente_nombre,
                        email: res.cliente_email || '-',
                        telefono: res.cliente_telefono || '-',
                        reservaciones: 0
                    };
                }
                clientsMap[key].reservaciones++;
            });

            const clients = Object.values(clientsMap);
            const tbody = document.getElementById('clients-table-body');

            if (clients.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state">No hay clientes registrados</td></tr>';
                return;
            }

            tbody.innerHTML = clients.map((c, i) => `
                <tr>
                    <td>${i + 1}</td>
                    <td>${c.nombre}</td>
                    <td>${c.email}</td>
                    <td>${c.telefono}</td>
                    <td><span class="badge badge-active">Activo</span></td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-info btn-sm" onclick="alert('Reservaciones: ${c.reservaciones}')">Ver</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        });
}

function filterClients() {
    const filter = document.getElementById('filter-cliente').value.toLowerCase();
    const rows = document.querySelectorAll('#clients-table-body tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

function showReservationForClient() {
    window.location.href = 'index.php?page=reservaciones';
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
