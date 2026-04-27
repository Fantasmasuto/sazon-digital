/**
 * Sazón Digital - Configuration JavaScript
 */

const diasSemana = {
    'lunes': 'Lunes',
    'martes': 'Martes',
    'miercoles': 'Miércoles',
    'jueves': 'Jueves',
    'viernes': 'Viernes',
    'sabado': 'Sábado',
    'domingo': 'Domingo'
};

document.addEventListener('DOMContentLoaded', function() {
    loadSchedules();
});

async function loadSchedules() {
    const horarios = await apiFetch('api/configuracion.php');
    if (!horarios || !Array.isArray(horarios)) return;

    const grid = document.getElementById('schedule-grid');

    grid.innerHTML = horarios.map(h => `
        <div class="schedule-card" data-dia="${h.dia_semana}">
            <h3>${diasSemana[h.dia_semana] || h.dia_semana}</h3>

            <div class="switch-group">
                <label class="toggle-switch">
                    <input type="checkbox" class="schedule-open" ${h.abierto ? 'checked' : ''}
                           onchange="toggleDayInputs('${h.dia_semana}', this.checked)">
                    <span class="toggle-slider"></span>
                </label>
                <span class="open-label">${h.abierto ? 'Abierto' : 'Cerrado'}</span>
            </div>

            <div class="time-inputs" id="time-inputs-${h.dia_semana}" style="${h.abierto ? '' : 'opacity: 0.5; pointer-events: none;'}">
                <div class="form-group">
                    <label>Hora de Apertura</label>
                    <input type="time" class="form-control schedule-apertura" value="${h.hora_apertura || '08:00'}">
                </div>
                <div class="form-group">
                    <label>Hora de Cierre</label>
                    <input type="time" class="form-control schedule-cierre" value="${h.hora_cierre || '22:00'}">
                </div>
            </div>

            <div style="margin-top: 10px;">
                <div class="switch-group">
                    <input type="checkbox" class="schedule-closed-all" id="closed-all-${h.dia_semana}">
                    <label for="closed-all-${h.dia_semana}" style="font-size: 0.8rem; color: #888;">Cerrado todo el día</label>
                </div>
            </div>
        </div>
    `).join('');

    // Add listeners for "closed all day" checkboxes
    document.querySelectorAll('.schedule-closed-all').forEach(cb => {
        cb.addEventListener('change', function() {
            const dia = this.id.replace('closed-all-', '');
            const card = document.querySelector(`[data-dia="${dia}"]`);
            const openCheck = card.querySelector('.schedule-open');
            if (this.checked) {
                openCheck.checked = false;
                toggleDayInputs(dia, false);
            }
        });
    });
}

function toggleDayInputs(dia, isOpen) {
    const timeInputs = document.getElementById('time-inputs-' + dia);
    const card = document.querySelector(`[data-dia="${dia}"]`);
    const label = card.querySelector('.open-label');

    if (isOpen) {
        timeInputs.style.opacity = '1';
        timeInputs.style.pointerEvents = 'auto';
        label.textContent = 'Abierto';
    } else {
        timeInputs.style.opacity = '0.5';
        timeInputs.style.pointerEvents = 'none';
        label.textContent = 'Cerrado';
    }
}

async function saveSchedules() {
    const horarios = {};
    const cards = document.querySelectorAll('.schedule-card');

    cards.forEach(card => {
        const dia = card.dataset.dia;
        const abierto = card.querySelector('.schedule-open').checked;
        const apertura = card.querySelector('.schedule-apertura').value;
        const cierre = card.querySelector('.schedule-cierre').value;

        horarios[dia] = {
            abierto: abierto,
            hora_apertura: apertura,
            hora_cierre: cierre
        };
    });

    const result = await apiFetch('api/configuracion.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ horarios: horarios })
    });

    if (result && result.success) {
        showNotification('Horarios actualizados exitosamente');
    } else {
        showNotification(result?.message || 'Error al guardar horarios', 'error');
    }
}
