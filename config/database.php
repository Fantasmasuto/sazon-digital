<?php
/**
 * ============================================
 * CONFIGURACIÓN DE BASE DE DATOS
 * ============================================
 *
 * Este archivo centraliza la conexión a MySQL.
 * Usamos PDO (PHP Data Objects) porque:
 *   - Es más seguro que mysqli (prepared statements automáticos)
 *   - Funciona con diferentes bases de datos (MySQL, PostgreSQL, etc.)
 *   - Es el estándar moderno de PHP
 *
 * XAMPP: Por defecto, MySQL usa usuario "root" sin contraseña.
 * Si cambiaste la contraseña en phpMyAdmin, actualiza DB_PASS.
 */

// Constantes de conexión (ajustar según tu XAMPP)
define('DB_HOST', 'localhost');       // Servidor MySQL (localhost en XAMPP)
define('DB_NAME', 'sazon_digital');   // Nombre de la base de datos
define('DB_USER', 'root');           // Usuario MySQL (root por defecto en XAMPP)
define('DB_PASS', '');               // Contraseña MySQL (vacía por defecto en XAMPP)
define('DB_CHARSET', 'utf8mb4');     // Charset que soporta emojis y caracteres especiales

/**
 * Crea y retorna una conexión PDO a la base de datos.
 *
 * ¿Por qué una función? Porque así cualquier archivo puede llamar
 * getConnection() sin preocuparse de cómo se crea la conexión.
 * Esto se llama "encapsulamiento" - un principio básico de programación.
 *
 * @return PDO Objeto de conexión a la base de datos
 */
function getConnection() {
    try {
        // DSN = Data Source Name - la "dirección" de nuestra base de datos
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        // Opciones de PDO para mejor manejo de errores y datos
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Lanzar excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Retornar arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Usar prepared statements reales
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;

    } catch (PDOException $e) {
        // Si falla la conexión, retornar error en formato JSON para que
        // las llamadas AJAX puedan interpretar el error correctamente.
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Error de conexión a la base de datos',
            'details' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
