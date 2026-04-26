<?php
/**
 * Controller: AuthController
 * Handles authentication actions
 */

require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../includes/helpers.php';

class AuthController {
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    // Handle login
    public function login() {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $rememberMe = isset($_POST['remember_me']);

        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email y contraseña son requeridos'];
        }

        return $this->authService->login($email, $password, $rememberMe);
    }

    // Handle logout
    public function logout() {
        $this->authService->logout();
        redirect('index.php?page=login');
    }
}
