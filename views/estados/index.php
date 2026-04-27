<?php
/**
 * View: Order States
 */
$pageTitle = 'Estados de Pedidos';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Vista Estados de Pedidos</h1>

<div class="data-table-wrapper" style="padding: 20px;">
    <div class="table-header">
        <h2>Buscar estado:</h2>
        <input type="text" class="form-control" id="filter-estado" placeholder="Buscar..." style="max-width: 200px;">
    </div>

    <ul class="states-list" id="states-list">
        <li class="empty-state">Cargando estados...</li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('api/pedidos.php?action=estados')
        .then(response => response.json())
        .then(states => {
            const list = document.getElementById('states-list');
            if (states.length === 0) {
                list.innerHTML = '<li class="empty-state">No hay estados configurados</li>';
                return;
            }
            list.innerHTML = states.map((state, index) => `
                <li>
                    <div class="state-number" style="background: ${state.color};">${index + 1}</div>
                    <div class="state-info">
                        <h3>${state.nombre}</h3>
                        <p>${state.descripcion || ''}</p>
                    </div>
                    <span class="badge" style="background: ${state.color}; margin-left: auto;">${state.nombre}</span>
                </li>
            `).join('');
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('states-list').innerHTML = '<li class="empty-state">Error al cargar estados</li>';
        });

    // Filter
    document.getElementById('filter-estado').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const items = document.querySelectorAll('#states-list li');
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(filter) ? '' : 'none';
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
