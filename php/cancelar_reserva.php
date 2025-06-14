<?php

session_start();

// Incluir la clase ReservasManager
require_once 'ReservasManager.php';

// Clase para manejar la cancelación de reservas
class CancelacionReservaController {
    private $reservasManager;
    private $usuarioId;
    
    public function __construct($usuarioId) {
        // Verificar si el usuario está logueado
        if (!isset($usuarioId)) {
            header('Location: login.php');
            exit;
        }
        
        $this->usuarioId = $usuarioId;
        $this->reservasManager = new ReservasManager($usuarioId);
    }
    
    public function cancelarReserva($reservaId) {
        if (!isset($reservaId) || empty($reservaId)) {
            $this->redirigirConError("No se especificó ninguna reserva para cancelar.");
            return;
        }
        
        try {
            // Verificar que la reserva pertenece al usuario actual
            // Cambiado de perteneceAUsuario a esReservaDelUsuario para que coincida con el método real
            if (!$this->reservasManager->esReservaDelUsuario($reservaId)) {
                throw new Exception("No se encontró la reserva o no tienes permiso para cancelarla.");
            }
            
            // Cancelar la reserva
            if (!$this->reservasManager->cancelarReserva($reservaId)) {
                throw new Exception("No se pudo cancelar la reserva. Inténtalo de nuevo.");
            }
            
            $this->redirigirConExito("Reserva cancelada con éxito.");
            
        } catch (Exception $e) {
            $this->redirigirConError($e->getMessage());
        }
    }
    
    private function redirigirConError($mensaje) {
        header('Location: mis_reservas.php?error=' . urlencode($mensaje));
        exit;
    }
    
    private function redirigirConExito($mensaje) {
        header('Location: mis_reservas.php?success=' . urlencode($mensaje));
        exit;
    }
}

// Uso del controlador
if (isset($_SESSION['usuario_id'])) {
    $controller = new CancelacionReservaController($_SESSION['usuario_id']);
    $controller->cancelarReserva($_GET['id'] ?? null);
} else {
    header('Location: login.php');
    exit;
}