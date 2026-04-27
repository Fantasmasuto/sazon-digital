/**
 * Sazón Digital - Reservations JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    loadReservations();
    loadTablesForReservation();

    // Form submission
    document.getElementById('reservation-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveReservation();
    });
});

// Load reservations
async function loadReservations() {
    const fecha = document.getElementById('filter-fecha-reservacion').value;
    let url = 'api/reservaciones.php';
    if (fecha) url += '?fecha=' + fecha;

    const reservations = await apiFetch(url);
    if (!reservations || !Array.isArray(reservations)) return;

    const tbody = document.getElementById('reservations-table-body');

    if (reservations.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="empty-state">No hay reservaciones</td></tr>';
        return;
    }

    // Admin y Mesero pueden gestionar reservaciones, Cocina solo puede ver
    const canManageReservations = (typeof USER_ROLE !== 'undefined' && USER_ROLE !== 'Cocina');

    tbody.innerHTML = reservations.map((res, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${res.cliente_nombre}</td>
            <td>${res.cliente_telefono || '-'}</td>
            <td>Mesa ${res.mesa_numero}</td>
            <td>${res.fecha}</td>
            <td>${res.hora_inicio} - ${res.hora_fin}</td>
            <td>${res.num_personas}</td>
            <td>
                <span class="badge ${getReservationBadge(res.estado)}">
                    ${res.estado}
                </span>
            </td>
            ${canManageReservations ? `
            <td>
                <div class="btn-group">
                    <button class="btn btn-warning btn-sm" onclick="editReservation(${res.id})">Editar</button>
                    ${res.estado === 'pendiente' ?
                        `<button class="btn btn-success btn-sm" onclick="updateReservationStatus(${res.id}, 'confirmada')">Confirmar</button>` : ''}
                    <button class="btn btn-danger btn-sm" onclick="deleteReservation(${res.id})">Eliminar</button>
                </div>
            </td>
            ` : '<td>-</td>'}
        </tr>
    `).join('');
}

function getReservationBadge(estado) {
    switch (estado) {
        case 'pendiente': return 'badge-pending';
        case 'confirmada': return 'badge-active';
        case 'cancelada': return 'badge-inactive';
        case 'completada': return 'badge-active';
        default: return '';
    }
}

// Load tables for dropdown
async function loadTablesForReservation() {
    const mesas = await apiFetch('api/pedidos.php?action=mesas');
    if (!mesas) return;

    const select = document.getElementById('res-mesa');
    // Clear existing options except the first one
    select.innerHTML = '<option value="">Seleccionar mesa</option>';

    // Use all tables (we check availability by date/time, not current occupancy)
    const allMesas = await apiFetch('api/pedidos.php?action=mesas');
    if (allMesas && Array.isArray(allMesas)) {
        allMesas.forEach(mesa => {
            const option = document.createElement('option');
            option.value = mesa.id;
            option.textContent = `Mesa ${mesa.numero} (${mesa.capacidad} personas)`;
            select.appendChild(option);
        });
    }
}

// Show modal
function showReservationModal(reservationData = null) {
    const modal = document.getElementById('reservation-modal');
    const title = document.getElementById('reservation-modal-title');

    if (reservationData) {
        title.textContent = 'Editar Reservación';
        document.getElementById('reservation-id').value = reservationData.id;
        document.getElementById('res-nombre').value = reservationData.cliente_nombre;
        document.getElementById('res-telefono').value = reservationData.cliente_telefono || '';
        document.getElementById('res-email').value = reservationData.cliente_email || '';
        document.getElementById('res-mesa').value = reservationData.mesa_id;
        document.getElementById('res-fecha').value = reservationData.fecha;
        document.getElementById('res-hora-inicio').value = reservationData.hora_inicio;
        document.getElementById('res-hora-fin').value = reservationData.hora_fin;
        document.getElementById('res-personas').value = reservationData.num_personas;
        document.getElementById('res-notas').value = reservationData.notas || '';
    } else {
        title.textContent = 'Nueva Reservación';
        document.getElementById('reservation-id').value = '';
        document.getElementById('reservation-form').reset();
    }

    modal.classList.add('active');
}

function closeReservationModal() {
    document.getElementById('reservation-modal').classList.remove('active');
}

// Edit reservation
async function editReservation(id) {
    const reservation = await apiFetch('api/reservaciones.php?id=' + id);
    if (reservation) {
        showReservationModal(reservation);
    }
}

// Save reservation (con validaciones)
async function saveReservation() {
    const id = document.getElementById('reservation-id').value;
    const telefono = document.getElementById('res-telefono').value.trim();
    const fecha = document.getElementById('res-fecha').value;
    const horaInicio = document.getElementById('res-hora-inicio').value;
    const horaFin = document.getElementById('res-hora-fin').value;
    const numPersonas = parseInt(document.getElementById('res-personas').value);

    // Validación 1: Teléfono debe tener exactamente 10 dígitos
    if (!/^\d{10}$/.test(telefono)) {
        showNotification('El teléfono debe tener exactamente 10 dígitos numéricos', 'error');
        return;
    }

    // Validación 2: Solo fechas de hoy en adelante
    const hoy = new Date().toISOString().split('T')[0];
    if (fecha < hoy) {
        showNotification('No se puede reservar en fechas pasadas. Selecciona de hoy en adelante.', 'error');
        return;
    }

    // Validación 3: Hora inicio debe ser antes que hora fin
    if (horaInicio >= horaFin) {
        showNotification('La hora de inicio debe ser antes que la hora de fin', 'error');
        return;
    }

    // Validación 4: Máximo 10 personas
    if (numPersonas < 1 || numPersonas > 10) {
        showNotification('El número de personas debe ser entre 1 y 10', 'error');
        return;
    }

    const data = {
        cliente_nombre: document.getElementById('res-nombre').value,
        cliente_telefono: telefono,
        cliente_email: document.getElementById('res-email').value,
        mesa_id: parseInt(document.getElementById('res-mesa').value),
        fecha: fecha,
        hora_inicio: horaInicio,
        hora_fin: horaFin,
        num_personas: numPersonas,
        notas: document.getElementById('res-notas').value
    };

    let url = 'api/reservaciones.php';
    let method = 'POST';

    if (id) {
        url += '?id=' + id;
        method = 'PUT';
    }

    const result = await apiFetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    if (result && result.success) {
        showNotification(result.message);
        closeReservationModal();
        loadReservations();
    } else {
        showNotification(result?.message || 'Error al guardar reservación', 'error');
    }
}

// Update reservation status
async function updateReservationStatus(id, estado) {
    const result = await apiFetch('api/reservaciones.php?id=' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ estado: estado })
    });

    if (result && result.success) {
        showNotification('Estado actualizado');
        loadReservations();
    } else {
        showNotification(result?.message || 'Error al actualizar', 'error');
    }
}

// Delete reservation
async function deleteReservation(id) {
    if (!confirmAction('¿Eliminar esta reservación?')) return;

    const result = await apiFetch('api/reservaciones.php?id=' + id, { method: 'DELETE' });
    if (result && result.success) {
        showNotification('Reservación eliminada');
        loadReservations();
    } else {
        showNotification(result?.message || 'Error al eliminar', 'error');
    }
}
