<?php

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Verificar si se recibió un ID de reserva válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: mis_reservas.php?error=No se especificó una reserva válida');
    exit;
}

$reserva_id = (int)$_GET['id'];

// Incluir el gestor de reservas
require_once 'ReservasManager.php';

// Inicializar el gestor con el ID del usuario actual
$reservasManager = new ReservasManager($_SESSION['usuario_id']);

// Intentar cancelar la reserva
if ($reservasManager->cancelarReserva($reserva_id)) {
    header('Location: mis_reservas.php?success=' . urlencode($reservasManager->getMensaje()));
} else {
    header('Location: mis_reservas.php?error=' . urlencode($reservasManager->getError()));
}
exit;