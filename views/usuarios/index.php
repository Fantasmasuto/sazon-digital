<?php
/**
 * View: Users List
 */
$pageTitle = 'Usuarios';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title">Vista Usuarios</h1>

<div class="data-table-wrapper">
    <div class="table-header">
        <div class="filters-bar">
            <label>Buscar usuarios:</label>
            <input type="text" class="form-control" id="filter-usuario" placeholder="Buscar..." onkeyup="filterUsers()">
        </div>
        <a href="index.php?page=usuario-form" class="btn btn-primary">Nuevo Usuario</a>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="users-table-body">
            <tr><td colspan="6" class="empty-state">Cargando usuarios...</td></tr>
        </tbody>
    </table>
</div>

<?php $pageScript = 'usuarios.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
