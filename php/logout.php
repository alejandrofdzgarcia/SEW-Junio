<?php
<?php
class LogoutManager {
    private $userManager;
    private $nombreUsuario;
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        require_once 'UserManager.php';
        
        $this->userManager = new UserManager();
        
        $this->nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';
    }
    
    public function esMetodoPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    public function procesarLogout() {
        $this->userManager->cerrarSesion();
        
        return '¡Hasta pronto, ' . $this->nombreUsuario . '!';
    }
    
    public function redirigir($mensaje = '') {
        header('Location: ../reservas.php' . ($mensaje ? '?mensaje=' . urlencode($mensaje) : ''));
        exit;
    }
}

$logoutManager = new LogoutManager();

if (!$logoutManager->esMetodoPost()) {
    $logoutManager->redirigir();
}

$mensajeDespedida = $logoutManager->procesarLogout();

$logoutManager->redirigir($mensajeDespedida);