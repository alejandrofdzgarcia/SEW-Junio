<?php

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Incluir la clase DBManager
require_once 'DBManager.php';

// Verificar si se ha proporcionado un ID de reserva
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mis_reservas.php?error=No se especificó ninguna reserva para cancelar.');
    exit;
}

$reserva_id = intval($_GET['id']);
$usuario_id = $_SESSION['usuario_id'];

try {
    // Inicializar el gestor de base de datos
    $dbManager = new DBManager();
    $db = $dbManager->getConnection();

    // Verificar que la reserva pertenece al usuario actual
    $query = "SELECT id FROM reservas WHERE id = ? AND usuario_id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('ii', $reserva_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("No se encontró la reserva o no tienes permiso para cancelarla.");
    }

    // Cancelar la reserva (actualizar el estado a 'cancelada')
    $query = "UPDATE reservas SET estado = 'cancelada' WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $reserva_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception("No se pudo cancelar la reserva. Inténtalo de nuevo.");
    }

    // Redirigir con un mensaje de éxito
    header('Location: mis_reservas.php?success=Reserva cancelada con éxito.');
    exit;

} catch (Exception $e) {
    // Redirigir con un mensaje de error
    header('Location: mis_reservas.php?error=' . urlencode($e->getMessage()));
    exit;
} finally {
    // Cerrar la conexión
    if (isset($dbManager)) {
        $dbManager->closeConnection();
    }
}