<?php
/**
 * Script para procesar el cierre de sesión utilizando UserManager
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 2.0
 */

session_start();

// Verificar que la petición es POST para mayor seguridad
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../reservas.php');
    exit;
}

// Incluir la clase UserManager
require_once 'UserManager.php';

// Crear instancia del gestor de usuarios
$userManager = new UserManager();

// Almacenar el nombre de usuario para mensaje de despedida
$nombreUsuario = isset($_SESSION['usuario_nombre']) ? $_SESSION['usuario_nombre'] : '';

// Cerrar la sesión usando el método del UserManager
$userManager->cerrarSesion();

// Redirigir a la página de reservas con mensaje de confirmación
header('Location: ../reservas.php?mensaje=Sesión cerrada correctamente. ¡Hasta pronto, ' . urlencode($nombreUsuario) . '!');
exit;
