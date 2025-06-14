<?php
/**
 * Script para visualizar las reservas del usuario actual
 * utilizando el paradigma orientado a objetos
 * 
 * @author Alejandro Fernández García - UO295813
 * @version 2.1
 */

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    // Si no está logueado, redirigir a la página de login
    header('Location: login.php');
    exit;
}

// Incluir la clase ReservasManager
require_once 'ReservasManager.php';

// Inicializar el gestor de reservas con el ID del usuario actual
$reservasManager = new ReservasManager($_SESSION['usuario_id']);

// Obtener las reservas del usuario actual
$reservas = $reservasManager->obtenerReservasUsuario();

// Obtener mensajes de error o éxito
$error = '';
$mensaje = '';

if (isset($_GET['success'])) {
    $mensaje = $_GET['success'];
}

if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Si hay error en el gestor, mostrarlo
if (empty($error) && $reservasManager->getError()) {
    $error = $reservasManager->getError();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../multimedia/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mis Reservas - Muros del Nalón</title>
    <meta name="author" content="Alejandro Fernández García"/>
    <meta name="description" content="Mis Reservas - Muros del Nalón"/>
    <meta name="keywords" content="reservas, usuario, turismo"/>    
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css">
</head>
<body>
    <header>
        <h1><a href="../index.html" title="Inicio Muros del Nalón">Muros del Nalón</a></h1>
        <nav>
            <a href="../index.html" title="Inicio Muros del Nalón">Página principal</a>
            <a href="../gastronomia.html" title="Gastronomía Muros del Nalón">Gastronomía</a>
            <a href="../rutas.html" title="Rutas Muros del Nalón">Rutas</a>
            <a href="../meteorologia.html" title="Meteorología Muros del Nalón">Meteorología</a>
            <a href="../juego.html" title="Juego Muros del Nalón">Juego</a>
            <a href="../reservas.php" title="Reservas Muros del Nalón">Reservas</a>
            <a href="../ayuda.html" title="Ayuda Muros del Nalón">Ayuda</a>
        </nav>
    </header>
    
    <p>Estás en: <a href="../index.html">Inicio</a> >> <a href="../reservas.php">Reservas</a> >> Mis Reservas</p>
    
    <main>
        <h1>Mis Reservas</h1>
        
        <?php if (!empty($mensaje)): ?>
            <p><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <section>
            <h2>Listado de Reservas</h2>
            
            <?php if (empty($reservas)): ?>
                <section>
                    <h3>No tienes reservas activas en este momento.</h3>
                    <p><a href="realizar_reserva.php">Realizar una reserva</a></p>
                </section>
            <?php else: ?>
                    <?php foreach ($reservas as $reserva): ?>
                        <article>
                            <h3><?php echo htmlspecialchars($reserva['recurso_nombre']); ?></h3>
                            <p>
                                <strong>Fechas:</strong> 
                                <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_hora_inicio'])); ?> - 
                                <?php echo date('d/m/Y H:i', strtotime($reserva['fecha_hora_fin'])); ?>
                            </p>
                            <p>
                                <strong>Personas:</strong> 
                                <?php echo $reserva['numero_personas']; ?>
                            </p>
                            <p>
                                <strong>Precio Total:</strong> 
                                <?php echo number_format($reserva['precio_total'], 2); ?>€
                            </p>
                            <p>
                                <strong>Estado:</strong> 
                                <?php echo htmlspecialchars($reserva['estado']); ?>
                            </p>
                            <p>
                                <strong>Descripción:</strong>
                                <?php echo htmlspecialchars($reserva['recurso_descripcion']); ?>
                            </p>
                            <section>
                                <h4>Fecha de Reserva:</h4>
                                <p>
                                    <span>
                                        <?php echo date('d/m/Y', strtotime($reserva['fecha_reserva'])); ?>
                                    </span>
                                </p>
                                <?php if (strtolower($reserva['estado']) !== 'cancelada'): ?>
                                <p>
                                    <a href="cancelar_reserva.php?id=<?php echo $reserva['id']; ?>">Cancelar</a>
                                </p>
                                <?php endif; ?>
                            </section>
                        </article>
                    <?php endforeach; ?>
                
                <section>
                    <h4>Acciones</h4>
                    <p>
                        <a href="realizar_reserva.php">Realizar otra reserva</a>
                        <a href="../reservas.php">Volver a Reservas</a>
                    </p>
                </section>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>2025 Turismo de Muros del Nalón</p>
        <p><a href="https://www.uniovi.es">Universidad de Oviedo</a> 
            - <a href="https://www.uniovi.es/estudia/grados/ingenieria/informaticasoftware/-/fof/asignatura/GIISOF01-3-002">Software y Estándares para la Web</a></p>
        <p><a href="https://github.com/alejandrofdzgarcia">Diseñado por Alejandro Fernández García</a></p>
    </footer>
</body>
</html>
