<?php
$currentUser = getCurrentUser();
$currentPage = $_GET['page'] ?? 'dashboard';
$userRole = strtolower($currentUser['rol'] ?? '');
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <img src="assets/img/logo.png" alt="Logo" class="sidebar-logo" onerror="this.style.display='none'">
        <h2>Sazón Digital</h2>
        <p>Gestión de Restaurantes</p>
    </div>
    <ul class="sidebar-nav">
        <li>
            <a href="index.php?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">&#9776;</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=productos" class="<?= $currentPage === 'productos' || $currentPage === 'producto-form' ? 'active' : '' ?>">
                <span class="nav-icon">&#9733;</span>
                <span>Productos</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=pedidos" class="<?= in_array($currentPage, ['pedidos','pedido-crear','pedido-detalle']) ? 'active' : '' ?>">
                <span class="nav-icon">&#128196;</span>
                <span>Pedidos</span>
            </a>
        </li>
        <?php if ($userRole === 'administrador'): ?>
        <li>
            <a href="index.php?page=usuarios" class="<?= in_array($currentPage, ['usuarios','usuario-form']) ? 'active' : '' ?>">
                <span class="nav-icon">&#128100;</span>
                <span>Usuarios</span>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="index.php?page=estados" class="<?= $currentPage === 'estados' ? 'active' : '' ?>">
                <span class="nav-icon">&#128203;</span>
                <span>Estados</span>
            </a>
        </li>
        <?php if ($userRole === 'administrador'): ?>
        <li>
            <a href="index.php?page=ventas" class="<?= $currentPage === 'ventas' ? 'active' : '' ?>">
                <span class="nav-icon">&#128176;</span>
                <span>Ventas</span>
            </a>
        </li>
        <?php endif; ?>
        <li>
            <a href="index.php?page=reservaciones" class="<?= $currentPage === 'reservaciones' ? 'active' : '' ?>">
                <span class="nav-icon">&#128197;</span>
                <span>Reservaciones</span>
            </a>
        </li>
        <?php if ($userRole === 'administrador'): ?>
        <li>
            <a href="index.php?page=configuracion" class="<?= $currentPage === 'configuracion' ? 'active' : '' ?>">
                <span class="nav-icon">&#9881;</span>
                <span>Horarios</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>
