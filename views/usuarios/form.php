<?php
/**
 * View: User Form (Create/Edit)
 */
$id = intval($_GET['id'] ?? 0);
$pageTitle = $id > 0 ? 'Editar Usuario' : 'Nuevo Usuario';
$currentUser = getCurrentUser();
require_once __DIR__ . '/../layout/header.php';
?>

<h1 class="page-title"><?= $pageTitle ?></h1>

<div class="form-container">
    <form id="user-form">
        <input type="hidden" id="user-id" value="<?= $id ?>">

        <div class="form-group">
            <label for="nombre">Nombre Completo *</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña <?= $id > 0 ? '(dejar vacío para no cambiar)' : '*' ?></label>
            <input type="password" class="form-control" id="password" name="password" <?= $id === 0 ? 'required' : '' ?>>
        </div>

        <div class="form-group">
            <label for="rol_id">Rol *</label>
            <select class="form-control" id="rol_id" name="rol_id" required>
                <option value="">Seleccionar rol</option>
            </select>
        </div>

        <div class="form-group">
            <label for="estado">Estado</label>
            <select class="form-control" id="estado" name="estado">
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            <a href="index.php?page=usuarios" class="btn btn-danger">Cancelar</a>
        </div>
    </form>
</div>

<?php $pageScript = 'usuarios.js'; ?>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
