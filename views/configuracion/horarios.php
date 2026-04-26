<?php
/**
 * View: Schedule Configuration
 */
$pageTitle = 'Configuración de Horarios';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Vista Horarios</h1>

<div class="schedule-grid" id="schedule-grid">
    <div class="empty-state">Cargando horarios...</div>
</div>

<div style="margin-top: 20px;">
    <button class="btn btn-primary" onclick="saveSchedules()">Guardar Horarios</button>
</div>

<?php $pageScript = 'configuracion.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
