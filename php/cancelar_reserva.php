<?php

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: mis_reservas.php?error=No se especificó una reserva válida');
    exit;
}

$reserva_id = (int)$_GET['id'];

require_once 'ReservasManager.php';

$reservasManager = new ReservasManager($_SESSION['usuario_id']);

if ($reservasManager->cancelarReserva($reserva_id)) {
    header('Location: mis_reservas.php?success=' . urlencode($reservasManager->getMensaje()));
} else {
    header('Location: mis_reservas.php?error=' . urlencode($reservasManager->getError()));
}
exit;