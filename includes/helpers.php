<?php
/**
 * ============================================
 * FUNCIONES AUXILIARES (HELPERS)
 * ============================================
 *
 * Este archivo contiene funciones reutilizables que se usan
 * en toda la aplicación. Son como "herramientas" que cualquier
 * parte del código puede usar.
 *
 * Concepto: En lugar de repetir código, lo ponemos en funciones.
 * Esto es el principio DRY (Don't Repeat Yourself).
 */

/**
 * Inicia la sesión PHP si no está iniciada.
 *
 * Las sesiones permiten guardar datos del usuario entre páginas.
 * Sin sesiones, cada petición HTTP sería "sin memoria".
 *
 * Ejemplo: Cuando haces login, guardamos tu ID en $_SESSION
 * y en la siguiente página sabemos que sigues logueado.
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica si el usuario está logueado.
 * Simplemente revisa si existe un ID de usuario en la sesión.
 *
 * @return bool true si está logueado, false si no
 */
function isLoggedIn() {
    initSession();
    return isset($_SESSION['usuario_id']);
}

/**
 * Obtiene los datos del usuario actual desde la sesión.
 * Retorna null si no hay usuario logueado.
 *
 * @return array|null Datos del usuario o null
 */
function getCurrentUser() {
    initSession();
    if (!isLoggedIn()) return null;
    return [
        'id'     => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'email'  => $_SESSION['usuario_email'],
        'rol'    => $_SESSION['usuario_rol'],
        'rol_id' => $_SESSION['usuario_rol_id']
    ];
}

/**
 * Verifica si el usuario actual tiene un rol específico.
 * Útil para mostrar/ocultar elementos según el rol.
 *
 * Ejemplo: if (hasRole('Administrador')) { ... }
 *
 * @param string $role Nombre del rol a verificar
 * @return bool true si tiene el rol
 */
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) return false;
    return strtolower($user['rol']) === strtolower($role);
}

/**
 * Redirecciona al usuario a otra URL.
 * Usa header() de PHP para enviar una redirección HTTP 302.
 * El exit es IMPORTANTE: sin él, el código seguiría ejecutándose.
 *
 * @param string $url URL destino
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Envía una respuesta JSON al cliente.
 * Se usa en los endpoints API para responder con datos estructurados.
 *
 * @param mixed $data Datos a enviar (se convierten a JSON automáticamente)
 * @param int $statusCode Código HTTP (200=OK, 201=Creado, 400=Error, etc.)
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Limpia un texto de entrada para prevenir ataques XSS.
 *
 * XSS = Cross-Site Scripting: cuando un atacante inyecta código
 * JavaScript malicioso a través de formularios.
 * htmlspecialchars() convierte caracteres especiales como < > " '
 * a sus equivalentes HTML seguros (&lt; &gt; etc.)
 *
 * @param string $input Texto a limpiar
 * @return string Texto limpio y seguro
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Obtiene la URL base de la aplicación.
 * Detecta automáticamente si usa HTTP o HTTPS.
 *
 * @return string URL base (ej: http://localhost/sazon-digital)
 */
function baseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    $path = rtrim($path, '/');
    return "$protocol://$host$path";
}

/**
 * Mensajes Flash: mensajes que se muestran UNA SOLA VEZ.
 *
 * Útiles para mostrar "Producto creado exitosamente" después
 * de una redirección. Se guardan en sesión y se eliminan
 * al ser leídos.
 *
 * Flujo: setFlash() -> redirect() -> getFlash() -> se muestra -> se borra
 */
function setFlash($type, $message) {
    initSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    initSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // Se borra después de leer (por eso es "flash")
        return $flash;
    }
    return null;
}

/**
 * Verifica si el restaurante está abierto en este momento.
 *
 * Regla de negocio: Consulta la tabla configuracion_horarios
 * para el día actual y compara con la hora actual del servidor.
 *
 * @return bool true si está abierto
 */
function isRestaurantOpen() {
    require_once __DIR__ . '/../config/database.php';
    $pdo = getConnection();

    // Mapeo de date('w'): 0=Domingo, 1=Lunes, ..., 6=Sábado
    $dias = ['domingo','lunes','martes','miercoles','jueves','viernes','sabado'];
    $hoy = $dias[date('w')];
    $horaActual = date('H:i:s');

    $stmt = $pdo->prepare("SELECT * FROM configuracion_horarios WHERE dia_semana = ?");
    $stmt->execute([$hoy]);
    $config = $stmt->fetch();

    // Si no hay configuración o el día está marcado como cerrado
    if (!$config || !$config['abierto']) {
        return false;
    }

    // Verificar si la hora actual está dentro del horario
    return ($horaActual >= $config['hora_apertura'] && $horaActual <= $config['hora_cierre']);
}

/**
 * Formatea un precio con símbolo de dólar y 2 decimales.
 * Ejemplo: formatPrice(12.5) retorna "$12.50"
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Formatea una fecha de MySQL a formato legible.
 * Ejemplo: "2024-07-20 14:30:00" -> "2024-07-20 14:30"
 */
function formatDate($date) {
    return date('Y-m-d H:i', strtotime($date));
}
