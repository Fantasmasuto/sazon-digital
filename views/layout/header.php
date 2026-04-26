<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sazón Digital - <?= $pageTitle ?? 'Sistema de Gestión' ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<div class="app-wrapper">
<?php require_once __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
    <!-- Top Header -->
    <div class="top-header">
        <div class="header-left">
            <h1>Sazón Digital</h1>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span>Nombre Usuario</span>
                <span><?= htmlspecialchars($currentUser['nombre'] ?? 'Usuario') ?></span>
            </div>
            <a href="index.php?page=logout" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>

    <?php
    // Show restaurant status
    require_once __DIR__ . '/../../services/ConfiguracionService.php';
    $configService = new ConfiguracionService();
    $isOpen = $configService->isOpen();
    ?>
    <div class="status-banner <?= $isOpen ? 'status-open' : 'status-closed' ?>">
        <?= $isOpen ? 'Restaurante ABIERTO' : 'Restaurante CERRADO - Operaciones bloqueadas' ?>
    </div>

    <!-- Flash Messages -->
    <?php $flash = getFlash(); if ($flash): ?>
    <div class="page-content" style="padding-bottom: 0;">
        <div class="flash-message flash-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="page-content">
