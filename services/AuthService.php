<?php
/**
 * Service: AuthService
 * Handles authentication business logic
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/helpers.php';

class AuthService {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    // Login with email and password
    public function login($email, $password, $rememberMe = false) {
        $user = $this->usuario->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email o contraseña incorrectos'];
        }

        if ($user['estado'] !== 'activo') {
            return ['success' => false, 'message' => 'Tu cuenta está inactiva. Contacta al administrador.'];
        }

        // Set session variables
        $this->setSession($user);

        // Handle remember me
        if ($rememberMe) {
            $this->createRememberToken($user['id']);
        }

        return ['success' => true, 'message' => 'Inicio de sesión exitoso'];
    }

    // Login with remember me token
    public function loginWithToken($token) {
        $user = $this->usuario->findByToken($token);

        if (!$user) {
            // Invalid or expired token - remove cookie
            setcookie('remember_token', '', time() - 3600, '/');
            return false;
        }

        if ($user['estado'] !== 'activo') {
            return false;
        }

        $this->setSession($user);
        return true;
    }

    // Set session variables
    private function setSession($user) {
        initSession();
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nombre'] = $user['nombre'];
        $_SESSION['usuario_email'] = $user['email'];
        $_SESSION['usuario_rol'] = $user['rol_nombre'];
        $_SESSION['usuario_rol_id'] = $user['rol_id'];
    }

    // Create remember me token
    private function createRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->usuario->saveToken($userId, $token, $expiry);

        // Set cookie for 30 days
        setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
    }

    // Logout
    public function logout() {
        initSession();

        // Delete remember token if exists
        if (isset($_COOKIE['remember_token'])) {
            $this->usuario->deleteToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Destroy session
        session_unset();
        session_destroy();
    }
}
