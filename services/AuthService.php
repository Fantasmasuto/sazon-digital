<?php
/**
 * ============================================
 * SERVICIO DE AUTENTICACIÓN
 * ============================================
 *
 * Maneja toda la lógica de inicio/cierre de sesión.
 *
 * ¿Por qué un servicio separado?
 * Porque la lógica de autenticación incluye varias operaciones:
 *   - Verificar contraseña
 *   - Crear sesión
 *   - Manejar cookies de "Recordarme"
 *   - Limpiar tokens al cerrar sesión
 *
 * Si pusiéramos todo esto en el controlador, sería difícil de mantener.
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/helpers.php';

class AuthService {
    private $usuario;

    public function __construct() {
        // Crear instancia del modelo para acceder a la base de datos
        $this->usuario = new Usuario();
    }

    /**
     * Inicio de sesión con email y contraseña.
     *
     * Proceso:
     * 1. Buscar usuario por email
     * 2. Verificar contraseña con password_verify() (compara hash)
     * 3. Verificar que la cuenta está activa
     * 4. Crear sesión PHP
     * 5. Si marcó "Recordarme", crear token persistente
     *
     * @param string $email Email del usuario
     * @param string $password Contraseña en texto plano
     * @param bool $rememberMe Si desea sesión persistente
     * @return array {success: bool, message: string}
     */
    public function login($email, $password, $rememberMe = false) {
        // Buscar usuario en la base de datos
        $user = $this->usuario->findByEmail($email);

        if (!$user) {
            // No revelar si el email existe o no (seguridad)
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        // password_verify() compara la contraseña con el hash almacenado
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        // Verificar que la cuenta no esté desactivada
        if ($user['estado'] !== 'activo') {
            return ['success' => false, 'message' => 'Tu cuenta está inactiva. Contacta al administrador.'];
        }

        // Login exitoso: crear sesión
        $this->setSession($user);

        // Si marcó "Recordarme", crear cookie persistente
        if ($rememberMe) {
            $this->createRememberToken($user['id']);
        }

        return ['success' => true, 'message' => 'Inicio de sesión exitoso'];
    }

    /**
     * Login automático usando token de "Recordarme".
     * Se ejecuta cuando el usuario tiene una cookie válida
     * pero su sesión PHP expiró.
     *
     * @param string $token Token almacenado en la cookie
     * @return bool true si el login fue exitoso
     */
    public function loginWithToken($token) {
        $user = $this->usuario->findByToken($token);

        if (!$user) {
            // Token inválido o expirado - eliminar cookie
            setcookie('remember_token', '', time() - 3600, '/');
            return false;
        }

        if ($user['estado'] !== 'activo') {
            return false;
        }

        $this->setSession($user);
        return true;
    }

    /**
     * Guarda los datos del usuario en la sesión PHP.
     * Estos datos estarán disponibles en todas las páginas
     * mientras la sesión esté activa.
     */
    private function setSession($user) {
        initSession();
        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_email']  = $user['email'];
        $_SESSION['usuario_rol']    = $user['rol_nombre'];
        $_SESSION['usuario_rol_id'] = $user['rol_id'];
    }

    /**
     * Crea un token para la función "Recordarme".
     *
     * Funcionamiento:
     * 1. Se genera un token aleatorio seguro (64 caracteres hex)
     * 2. Se guarda en la base de datos con fecha de expiración
     * 3. Se envía al navegador como cookie (válida 30 días)
     * 4. Si la sesión PHP expira, el middleware lee la cookie
     *    y auto-autentica al usuario con este token
     *
     * @param int $userId ID del usuario
     */
    private function createRememberToken($userId) {
        // random_bytes(32) genera 32 bytes criptográficamente seguros
        // bin2hex() los convierte a string hexadecimal (64 caracteres)
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Guardar token en la base de datos
        $this->usuario->saveToken($userId, $token, $expiry);

        // Enviar cookie al navegador (86400 = segundos en un día)
        // httponly=true: no accesible desde JavaScript (seguridad contra XSS)
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
    }

    /**
     * Cierre de sesión.
     * 1. Elimina el token de "Recordarme" de la BD
     * 2. Elimina la cookie del navegador
     * 3. Destruye la sesión PHP
     */
    public function logout() {
        initSession();

        // Eliminar token de recordarme si existe
        if (isset($_COOKIE['remember_token'])) {
            $this->usuario->deleteToken($_COOKIE['remember_token']);
            // Eliminar cookie estableciendo fecha pasada
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Destruir sesión PHP
        session_unset();     // Elimina todas las variables de sesión
        session_destroy();   // Destruye la sesión
    }
}
