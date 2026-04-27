<?php
/**
 * ============================================
 * SCRIPT DE INSTALACIÓN - SAZÓN DIGITAL
 * ============================================
 *
 * Este script se ejecuta UNA SOLA VEZ para:
 * 1. Crear la base de datos
 * 2. Crear las tablas
 * 3. Insertar datos iniciales con contraseñas encriptadas
 *
 * INSTRUCCIONES:
 *   1. Copiar proyecto a C:\xampp\htdocs\sazon-digital\
 *   2. Iniciar Apache y MySQL desde XAMPP
 *   3. Abrir en el navegador: http://localhost/sazon-digital/install.php
 *   4. Después de instalar, ELIMINAR este archivo por seguridad
 *
 * IMPORTANTE: Si prefieres usar phpMyAdmin, importa database/schema.sql
 * en su lugar. Pero con este script las contraseñas se generan correctamente.
 */

// ============================================
// CONFIGURACIÓN (debe coincidir con config/database.php)
// ============================================
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sazon_digital';
$charset = 'utf8mb4';

// Contraseña por defecto para todos los usuarios de prueba
$defaultPassword = 'password123';

echo "<html><head><title>Instalación - Sazón Digital</title>";
echo "<style>
    body { font-family: 'Segoe UI', sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
    h1 { color: #FF6B00; }
    .success { color: #27ae60; }
    .error { color: #e74c3c; }
    .info { color: #3498db; }
    .step { background: #fff; padding: 12px; margin: 8px 0; border-radius: 6px; border-left: 4px solid #FF6B00; }
    .credentials { background: #1a1a2e; color: #fff; padding: 20px; border-radius: 8px; margin: 15px 0; }
    .credentials table { width: 100%; border-collapse: collapse; }
    .credentials th, .credentials td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #333; }
    .credentials th { color: #FF6B00; }
    a.btn { display: inline-block; background: #FF6B00; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none; margin-top: 15px; }
</style></head><body>";
echo "<h1>Instalación de Sazón Digital</h1>";

try {
    // Paso 1: Conectar a MySQL (sin seleccionar base de datos)
    echo "<div class='step'><span class='info'>Paso 1:</span> Conectando a MySQL...</div>";
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<div class='step'><span class='success'>Conexión exitosa</span></div>";

    // Paso 2: Crear base de datos
    echo "<div class='step'><span class='info'>Paso 2:</span> Creando base de datos '$dbname'...</div>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE `$dbname`");
    echo "<div class='step'><span class='success'>Base de datos creada</span></div>";

    // Paso 3: Ejecutar esquema SQL (sin los INSERTs - los haremos con PHP)
    echo "<div class='step'><span class='info'>Paso 3:</span> Creando tablas...</div>";

    // Leer archivo SQL y extraer solo CREATE TABLEs
    $sqlFile = file_get_contents(__DIR__ . '/database/schema.sql');

    // Ejecutar todo el SQL (incluye CREATE TABLE e INSERTs)
    // Pero primero eliminar los INSERTs de usuarios (los haremos con hash PHP)
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Ejecutar el esquema completo
    $statements = array_filter(
        array_map('trim', explode(';', $sqlFile)),
        function($s) {
            $s = trim($s);
            // Omitir líneas vacías, comentarios solos, CREATE DATABASE, USE
            if (empty($s)) return false;
            if (strpos($s, 'CREATE DATABASE') !== false) return false;
            if (strpos($s, 'USE ') === 0) return false;
            return true;
        }
    );

    foreach ($statements as $stmt) {
        try {
            $pdo->exec($stmt);
        } catch (PDOException $e) {
            // Ignorar errores de "table already exists" o "duplicate entry"
            if (strpos($e->getMessage(), '1062') === false && strpos($e->getMessage(), '1050') === false) {
                echo "<div class='step'><span class='error'>Advertencia: " . $e->getMessage() . "</span></div>";
            }
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div class='step'><span class='success'>Tablas creadas exitosamente</span></div>";

    // Paso 4: Verificar que los usuarios de prueba tienen contraseñas válidas
    echo "<div class='step'><span class='info'>Paso 4:</span> Verificando contraseñas de usuarios de prueba...</div>";

    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);

    // Actualizar todas las contraseñas con hashes PHP válidos
    $stmtUpdate = $pdo->prepare("UPDATE usuarios SET password = ?");
    $stmtUpdate->execute([$hash]);
    $count = $stmtUpdate->rowCount();

    echo "<div class='step'><span class='success'>$count usuarios actualizados con contraseñas seguras</span></div>";

    // Paso 5: Mostrar credenciales
    echo "<div class='step'><span class='info'>Paso 5:</span> ¡Instalación completada!</div>";

    echo "<div class='credentials'>";
    echo "<h3 style='color: #FF6B00; margin-top: 0;'>Usuarios de Prueba</h3>";
    echo "<p style='color: #aaa;'>Contraseña para todos: <strong style='color: #fff;'>$defaultPassword</strong></p>";
    echo "<table>";
    echo "<tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Estado</th></tr>";

    $users = $pdo->query("SELECT u.*, r.nombre as rol FROM usuarios u JOIN roles r ON u.rol_id = r.id ORDER BY u.id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $u) {
        $estadoColor = $u['estado'] === 'activo' ? '#27ae60' : '#e74c3c';
        echo "<tr>";
        echo "<td>{$u['nombre']}</td>";
        echo "<td>{$u['email']}</td>";
        echo "<td>{$u['rol']}</td>";
        echo "<td style='color: $estadoColor;'>{$u['estado']}</td>";
        echo "</tr>";
    }
    echo "</table></div>";

    echo "<a class='btn' href='index.php'>Ir al Login</a>";
    echo "<p style='color: #e74c3c; margin-top: 20px;'><strong>SEGURIDAD:</strong> Elimina este archivo (install.php) después de la instalación.</p>";

} catch (PDOException $e) {
    echo "<div class='step'><span class='error'>Error: " . $e->getMessage() . "</span></div>";
    echo "<p>Verifica que:</p>";
    echo "<ul>";
    echo "<li>XAMPP está ejecutándose (Apache + MySQL)</li>";
    echo "<li>El usuario MySQL es 'root' sin contraseña</li>";
    echo "<li>El puerto 3306 no está ocupado por otro programa</li>";
    echo "</ul>";
}

echo "</body></html>";
